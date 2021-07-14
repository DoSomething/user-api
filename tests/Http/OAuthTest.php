<?php

namespace Tests\Http;

use App\Models\Client;
use App\Models\User;
use Tests\BrowserKitTestCase;

class OAuthTest extends BrowserKitTestCase
{
    /**
     * A custom PHPUnit assertion to validate an OAuth response & JWT token claims.
     *
     * @param $user
     * @param $client
     * @param array $scopes
     */
    protected function assertValidJwtToken($user, $client, array $scopes)
    {
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token',
        ]);

        // Parse the token we received to see it's built correctly.
        $token = $this->response->json('access_token');
        $jwt = (new \Lcobucci\JWT\Parser())->parse($token);

        // Check that the token has the expected user ID and scopes.
        $this->assertSame($user->id, $jwt->getClaim('sub'));
        $this->assertSame('user', $jwt->getClaim('role'));
        $this->assertSame($scopes, $jwt->getClaim('scopes'));

        // Check that a refresh token was saved to the database.
        $this->seeInMongoDatabase('refresh_tokens', [
            'user_id' => $user->id,
            'client_id' => $client->client_id,
        ]);
    }

    /**
     * Test that the authorization code grant provides a JWT for valid credentials.
     */
    public function testAuthorizationCodeGrant()
    {
        $user = factory(User::class)->create(['password' => 'secret']);
        $client = factory(Client::class)
            ->states('authorization_code')
            ->create([
                'redirect_uri' => 'http://example.com/',
            ]);

        // Make the authorization request:
        $this->be($user, 'web');
        $this->get(
            'authorize?' .
                http_build_query([
                    'response_type' => 'code',
                    'client_id' => $client->client_id,
                    'client_secret' => $client->client_secret,
                    'redirect_uri' => $client->redirect_uri[0],
                    'scope' => 'user role:staff',
                    'state' => csrf_token(),
                ]),
        );

        // For the purpose of the the test, let's just grab the 'code' from the redirect.
        $redirect = $this->response->headers->get('location');
        $code = urldecode(
            str_replace('http://example.com/?code=', '', $redirect),
        );
        $this->assertNotEmpty(
            $code,
            'A code was returned to the redirect URI.',
        );

        // Freeze time so we can assert when we made this token.
        $now = $this->mockTime('+1 minute');

        // Finally, use that code to request a token:
        $this->post('v2/auth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'redirect_uri' => $client->redirect_uri[0],
            'code' => $code,
        ]);

        // A valid JWT should be returned, and the user's "last accessed" timestamp should update.
        $this->assertValidJwtToken($user, $client, ['user', 'role:staff']);
        $this->assertEquals(
            (string) $now,
            (string) $user->fresh()->last_accessed_at,
        );
    }

    /**
     * Test that the password grant provides a JWT for valid credentials.
     */
    public function testPasswordGrant()
    {
        $client = factory(Client::class)
            ->states('password')
            ->create();
        $user = factory(User::class)->create(['password' => 'secret']);

        $this->expectsEvents(\Illuminate\Auth\Events\Login::class);

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user->email,
            'password' => 'secret',
            'scope' => 'user profile',
        ]);

        $this->assertValidJwtToken($user, $client, ['user', 'profile']);
    }

    /**
     * Test that the password grant provides a JWT for valid credentials.
     */
    public function testRoleClaim()
    {
        $client = factory(Client::class)
            ->states('password')
            ->create();
        $admin = factory(User::class)
            ->states('admin')
            ->create([
                'password' => 'secret',
            ]);

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $admin->email,
            'password' => 'secret',
            'scope' => 'admin user',
        ]);

        // Parse the token we received to see it's built correctly.
        $token = $this->response->json('access_token');
        $jwt = (new \Lcobucci\JWT\Parser())->parse($token);
        $this->assertSame('admin', $jwt->getClaim('role'));
    }

    /**
     * Test that the password grant rejects invalid credentials.
     */
    public function testPasswordGrantWithInvalidCredentials()
    {
        $client = factory(Client::class)
            ->states('password')
            ->create();
        $user = factory(User::class)->create(['password' => 'secret']);

        $this->expectsEvents(\Illuminate\Auth\Events\Failed::class);

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user->email,
            'password' => 'letmein',
        ]);

        $this->assertResponseStatus(400);
    }

    /**
     * Test that the client credentials grant rejects invalid credentials.
     */
    public function testClientCredentialsGrantWithFakeClient()
    {
        factory(Client::class)
            ->states('client_credentials')
            ->create();

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'totally_legit_client',
            'client_secret' => 'banana', // <-- not the real client secret
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that the client credentials grant will not return "trusted" clients
     * if the client_secret is not provided.
     */
    public function testClientCredentialsGrantWithMissingSecret()
    {
        $client = factory(Client::class)
            ->states('client_credentials')
            ->create();

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->client_id,
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that the client credentials grant rejects invalid credentials.
     */
    public function testClientCredentialsGrantWithInvalidCredentials()
    {
        $client = factory(Client::class)
            ->states('client_credentials')
            ->create();

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->client_id,
            'client_secret' => 'banana',
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that we do not rate limit valid credentials.
     */
    public function testValidClientCredentialsAreNotRateLimited()
    {
        $client = factory(Client::class)
            ->states('client_credentials')
            ->create();

        $credentials = [
            'grant_type' => 'client_credentials',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
        ];

        for ($i = 0; $i < 15; $i++) {
            $this->post('v2/auth/token', $credentials);
            $this->assertResponseOk();
        }
    }

    /**
     * Test that requests are rate limited after 10 invalid attempts.
     */
    public function testInvalidClientCredentialsAreRateLimited()
    {
        $invalidCredentials = [
            'grant_type' => 'client_credentials',
            'client_id' => 'phpunit',
            'client_secret' => 'banana',
        ];

        for ($i = 0; $i < 10; $i++) {
            $this->post('v2/auth/token', $invalidCredentials);
            $this->assertResponseStatus(401);
        }

        // This next request should trigger a StatHat counter.
        $this->expectsEvents(\App\Events\Throttled::class);

        $this->post('v2/auth/token', $invalidCredentials);
        $this->assertResponseStatus(429);
    }

    /**
     * Test that clients can be granted a subset of their allowed scopes.
     */
    public function testRequestSubsetOfClientScopes()
    {
        $client = factory(Client::class)
            ->states('client_credentials')
            ->create([
                'scope' => ['admin', 'user'],
            ]);

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'scope' => 'user',
        ]);

        // We should receive a token with only the requested scopes.
        $jwt = (new \Lcobucci\JWT\Parser())->parse(
            $this->response->json('access_token'),
        );
        $this->assertSame(['user'], $jwt->getClaim('scopes'));
    }

    /**
     * Test that clients cannot be granted a scope that hasn't been
     * whitelisted for that client.
     */
    public function testCantRequestDisallowedClientScope()
    {
        $client = factory(Client::class)
            ->states('client_credentials')
            ->create([
                'scope' => ['user'],
            ]);

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'scope' => 'user admin',
        ]);

        // We should receive a token, but *not* with the disallowed scope
        $jwt = (new \Lcobucci\JWT\Parser())->parse(
            $this->response->json('access_token'),
        );
        $this->assertSame(['user'], $jwt->getClaim('scopes'));
    }

    /**
     * Test that clients cannot be granted a scope that doesn't exist.
     */
    public function testCantRequestFakeClientScope()
    {
        $client = factory(Client::class)
            ->states('client_credentials')
            ->create();

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'scope' => 'dog',
        ]);

        $this->assertResponseStatus(400);
    }

    /**
     * Test that the client credentials grant provides a JWT for valid credentials.
     */
    public function testClientCredentials()
    {
        $client = factory(Client::class)
            ->states('client_credentials')
            ->create();

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure(['token_type', 'expires_in', 'access_token']);

        $jwt = (new \Lcobucci\JWT\Parser())->parse(
            $this->response->json('access_token'),
        );

        // Check that the token has the expected user ID and scopes.
        $this->assertSame('', $jwt->getClaim('sub'));
        $this->assertSame('', $jwt->getClaim('role'));
        $this->assertSame([], $jwt->getClaim('scopes'));
    }

    /**
     * Test that the refresh token grant provides a new JWT in exchange for
     * an unused refresh token, and then invalidates that refresh token.
     */
    public function testRefreshTokenGrant()
    {
        $user = factory(User::class)->create(['password' => 'secret']);
        $client = factory(Client::class)
            ->states('password')
            ->create();

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user->email,
            'password' => 'secret',
        ]);

        // Get the provided refresh token.
        $refreshToken = $this->response->json('refresh_token');

        // Freeze time so we can assert when we made this token.
        $now = $this->mockTime('+1 minute');

        // Get a new access token using the refresh token.
        $this->post('v2/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'refresh_token' => $refreshToken,
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token',
        ]);

        // The user's `last_accessed_at` timestamp should be updated.
        $this->assertEquals(
            (string) $now,
            (string) $user->fresh()->last_accessed_at,
        );

        // And now, verify that that refresh token has been consumed.
        $this->post('v2/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'refresh_token' => $refreshToken,
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that an access token can be used to access a protected route.
     */
    public function testAccessToken()
    {
        $user = factory(User::class)
            ->states('admin')
            ->create(['password' => 'secret']);
        $client = factory(Client::class)
            ->states('password')
            ->create();

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user->email,
            'password' => 'secret',
            'scope' => 'user role:staff role:admin',
        ]);

        $token = $this->response->json('access_token');

        $this->get('v1/users', ['Authorization' => 'Bearer ' . $token]);
        $this->assertResponseStatus(200);
    }

    /**
     * Test that the user info route requires an access token.
     */
    public function testUserInfoAnonymous()
    {
        $this->json('GET', 'v2/auth/info');
        $this->assertResponseStatus(401);
    }

    /**
     * Test that an authenticated user can see their profile data, formatted
     * according to the OpenID Connect spec.
     */
    public function testUserInfo()
    {
        $this->asNormalUser()->json('GET', 'v2/auth/info');
        $this->assertResponseStatus(200);

        $this->seeJsonStructure([
            'data' => [
                'given_name',
                'family_name',
                'email',
                'phone_number',
                'birthdate',

                'address' => [
                    'street_address',
                    'locality',
                    'region',
                    'postal_code',
                    'country',
                ],

                'updated_at',
                'created_at',
            ],
        ]);
    }

    /**
     * Test that a refresh token can be revoked.
     */
    public function testRevokeRefreshToken()
    {
        $user = factory(User::class)->create(['password' => 'secret']);

        $client = factory(Client::class)
            ->states('password')
            ->create();

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user->email,
            'password' => 'secret',
            'scope' => 'admin user',
        ]);

        $jwt = $this->response->json();

        // Now, delete that refresh token.
        $this->delete(
            'v2/auth/token',
            [
                'token' => $jwt['refresh_token'],
            ],
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Bearer ' . $jwt['access_token'],
            ],
        );
        $this->assertResponseStatus(200);

        // And that token should now be rejected if provided:
        $this->post('v2/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'scope' => 'admin user',
            'refresh_token' => $jwt['refresh_token'],
        ]);
        $this->assertResponseStatus(401);
    }

    /**
     * Test that a made up refresh token does not cause an error.
     */
    public function testCantRevokeAnotherUsersRefreshToken()
    {
        $user1 = factory(User::class)->create([
            'password' => 'rather-secret-phrase',
        ]);
        $user2 = factory(User::class)->create([
            'password' => 'another-secret-code',
        ]);
        $client = factory(Client::class)
            ->states('password')
            ->create();

        // Make token for user #1.
        $jwt1 = $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user1->email,
            'password' => 'rather-secret-phrase',
            'scope' => 'user',
        ])->response->json();

        // Hacks. OAuth server seems to get mad if more than one request is made per request.
        $this->refreshApplication();

        // Make token for user #2.
        $jwt2 = $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user2->email,
            'password' => 'another-secret-code',
            'scope' => 'user',
        ])->response->json();

        // Now, try to delete User #1's refresh token w/ User #2's access token.
        $this->delete(
            'v2/auth/token',
            [
                'token' => $jwt1['refresh_token'], // <--- User #1's refresh token
            ],
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Bearer ' . $jwt2['access_token'], // <-- but User #2's access token!
            ],
        );
        $this->assertResponseStatus(401);
    }
}
