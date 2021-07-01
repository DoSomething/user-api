<?php

namespace Tests\Http\Web;

use App\Models\Client;
use App\Models\User;
use Tests\TestCase;

class WebAuthenticationTest extends TestCase
{
    /**
     * Default headers for this test case.
     *
     * @var array
     */
    protected $headers = [
        'Accept' => 'text/html',
    ];

    /**
     * Test that the homepage redirects to login page.
     */
    public function testHomepageAnonymousRedirect()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/register');
    }

    /**
     * Test that the profile renders for logged-in users.
     */
    public function testHomepage()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'web');

        $response = $this->followingRedirects()->get('/');

        $response->assertStatus(200);
        $response->assertSeeText('You are logged in as');
    }

    /**
     * Test that users can log in via the /login route.
     */
    public function testLogin()
    {
        $user = factory(User::class)->create([
            'email' => 'login-test@dosomething.org',
            'password' => 'secret',
        ]);

        $this->expectsEvents(\Illuminate\Auth\Events\Login::class);

        $this->post('/login', [
            'username' => 'Login-Test@dosomething.org',
            'password' => 'secret',
        ]);

        $this->assertAuthenticatedAs($user, 'web');
    }

    /**
     * Test that users cannot login with bad credentials via the web.
     */
    public function testLoginWithInvalidCredentials()
    {
        factory(User::class)->create([
            'email' => 'login-test@dosomething.org',
            'password' => 'secret',
        ]);

        $this->expectsEvents(\Illuminate\Auth\Events\Failed::class);

        $response = $this->post('/login', [
            'username' => 'Login-Test@dosomething.org',
            'password' => 'open-sesame', // <-- wrong password!
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors([
            'username' => 'These credentials do not match our records.',
        ]);
    }

    /**
     * Test that users can't brute-force the login form.
     */
    public function testLoginRateLimited()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', [
                'username' => 'target@example.com',
                'password' => 'password' . $i,
            ])->assertSessionHasErrors([
                'username' => 'These credentials do not match our records.',
            ]);
        }

        $this->expectsEvents(\App\Events\Throttled::class);

        $this->post('/login', [
            'username' => 'target@example.com',
            'password' => 'password11', // our attacker is very methodical.
        ])->assertSessionHas(
            'flash',
            'Too many attempts. Please try again in 15 minutes.',
        );
    }

    /**
     * Test that users who do not have a password on their account
     * are asked to reset it.
     */
    public function testLoginWithoutPasswordSet()
    {
        factory(User::class)->create([
            'email' => 'puppet-sloth@dosomething.org',
            'password' => null,
        ]);

        // Puppet Sloth doesn't have a DS.org password yet, but he tries to enter
        // "next-question" because that's his password everywhere else.
        $this->post('/login', [
            'username' => 'puppet-sloth@dosomething.org',
            'password' => 'next-question',
        ])->assertSessionHas('request_reset', true);
    }

    /**
     * Test that an authenticated user can log out.
     */
    public function testLogout()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'web');

        $response = $this->get('logout');

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $this->followRedirects($response);

        $this->assertGuest('web');
    }

    /**
     * Test that we can specify a custom post-logout redirect.
     */
    public function testLogoutRedirect()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'web');

        $response = $this->get(
            'logout?redirect=http://dev.dosomething.org:8888',
        );

        $response->assertStatus(302);
        $response->assertRedirect('http://dev.dosomething.org:8888');

        $this->assertGuest('web');
    }

    /**
     * Test that we can't be redirected to a third party domain
     * in the custom post-logout redirect.
     */
    public function testLogoutRedirectThirdPartyDomain()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'web');

        $response = $this->get(
            'logout?redirect=http://dosomething.org.sloth.com',
        );

        $response->assertStatus(302);
        $response->assertRedirect('/login'); // Not third party domain sloth.com

        $this->assertGuest('web');
    }

    /**
     * Test that users can register via the web.
     */
    public function testRegisterBeta()
    {
        $this->withHeader('X-Fastly-Country-Code', 'US')
            ->withHeader('X-Fastly-Postal-Code', '10010')
            ->withHeader('X-Fastly-Region-Code', 'CA')
            ->registerUpdated();

        $this->isAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        $this->assertEquals('US', $user->country);
        $this->assertEquals('en', $user->language);
        $this->assertEquals('10010', $user->addr_zip);
        $this->assertEquals('CA', $user->addr_state);

        // The user should be signed up for email messaging.
        $this->assertEquals(true, $user->email_subscription_status);
        $this->assertEquals(['community'], $user->email_subscription_topics);
    }

    /**
     * Test that users can register & then log in with the same credentials.
     */
    public function testRegisterAndLogin()
    {
        $this->post('/register', [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => 'register-and-login@example.org',
            'password' => 'my-top-secret-passphrase',
        ]);

        $this->isAuthenticated('web');

        auth('web')->logout();

        $this->post('/login', [
            'username' => 'register-and-login@example.org',
            'password' => 'my-top-secret-passphrase',
        ]);

        $this->isAuthenticated('web');
    }

    /**
     * Test that a referrer_user_id in session is attached to the registering user.
     */
    public function testRegisterBetaWithReferrerUserId()
    {
        $referrerUserId = factory(User::class)->create()->id;

        // Mock a session for the user with ?referrer_user_id=x param, indicating a referred user.
        $this->withSession([
            'referrer_user_id' => $referrerUserId,
        ])->registerUpdated();

        $this->isAuthenticated('web');

        $user = auth()->user();

        $this->assertEquals($referrerUserId, $user->referrer_user_id);
    }

    /**
     * Test that an invalid referrer_user_id in session is not attached to the registering user.
     */
    public function testRegisterBetaWithInvalidReferrerUserId()
    {
        // Mock a session for the user with ?referrer_user_id=x param, indicating a referred user.
        $this->withSession(['referrer_user_id' => '123'])->registerUpdated();

        $this->isAuthenticated('web');

        $user = auth()->user();

        $this->assertEquals(null, $user->referrer_user_id);
    }

    /**
     * Test that users get feature_flags values when tests are on.
     */
    public function testRegisterBetaWithFeatureFlagsTest()
    {
        // Turn on the badges and refer-friends-scholarship test feature flags.
        config([
            'features.badges' => true,
            'features.refer-friends-scholarship' => true,
        ]);

        $this->withHeader('X-Fastly-Country-Code', 'US')->registerUpdated();

        $this->isAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        // The user should have a value set for 'badges'
        $this->assertArrayHasKey('badges', $user->feature_flags);
        // The user should have true set for 'refer-friends-scholarship'
        $this->assertEquals(
            true,
            $user->feature_flags['refer-friends-scholarship'],
        );
    }

    /**
     * Test that club referrals do not get feature flags set when the badges and refer-friends-scholarship
     * tests are on.
     */
    public function testRegisterBetaFromClubsWithoutFeatureFlagsTest()
    {
        // Turn on badges and refer-friends-scholarship feature flags.
        config([
            'features.badges' => true,
            'features.refer-friends-scholarship' => true,
        ]);

        // Mock a session for the user with a ?utm_source=clubs param, indicating a clubs referral
        $this->withSession(['source_detail' => ['utm_source' => 'clubs']])
            ->withHeader('X-Fastly-Country-Code', 'US')
            ->withHeader('X-Fastly-Postal-Code', '10010')
            ->withHeader('X-Fastly-Region-Code', 'CA')
            ->registerUpdated();

        $this->isAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        $this->assertEquals('US', $user->country);
        $this->assertEquals('en', $user->language);
        $this->assertEquals('10010', $user->addr_zip);
        $this->assertEquals('CA', $user->addr_state);

        // The user should not have any `feature_flags`.
        $this->assertEquals(true, is_null($user->feature_flags));
    }

    /**
     * Test that users get refer-friends-scholarship feature_flag value when test is on, and no badges
     * value when test is off.
     */
    public function testRegisterBetaWithReferFriendsAndNoBadgesTest()
    {
        // Turn off badges, turn on refer-friends-scholarship test feature flags.
        config([
            'features.badges' => false,
            'features.refer-friends-scholarship' => true,
        ]);

        $this->withHeader('X-Fastly-Country-Code', 'US')->registerUpdated();

        $this->isAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        // The user should not have a value set for 'badges'
        $this->assertArrayNotHasKey('badges', $user->feature_flags);
        // The user should have true set for 'refer-friends-scholarship'
        $this->assertEquals(
            true,
            $user->feature_flags['refer-friends-scholarship'],
        );
    }

    /**
     * Test that users get badges feature_flag value when test is on, and no refer-friends-scholarship
     * value when test is off.
     */
    public function testRegisterBetaWithBadgesAndNoReferFriendsTest()
    {
        // Turn on badges, turn off refer-friends-scholarship test feature flags.
        config([
            'features.badges' => true,
            'features.refer-friends-scholarship' => false,
        ]);

        $this->withHeader('X-Fastly-Country-Code', 'US')->registerUpdated();

        $this->isAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        // The user should have a value set for 'badges'
        $this->assertArrayHasKey('badges', $user->feature_flags);
        // The user should not have a value set for 'refer-friends-scholarship'
        $this->assertArrayNotHasKey(
            'refer-friends-scholarship',
            $user->feature_flags,
        );
    }

    /**
     * Test that users do not get feature flags set when the badges and refer-friends-scholarship
     * tests are off.
     */
    public function testRegisterBetaWithoutFeatureFlagTests()
    {
        // Turn off the badges and refer-friends-scholarship test feature flags.
        config([
            'features.badges' => false,
            'features.refer-friends-scholarship' => false,
        ]);

        $this->withHeader('X-Fastly-Country-Code', 'US')
            ->withHeader('X-Fastly-Postal-Code', '10010')
            ->withHeader('X-Fastly-Region-Code', 'CA')
            ->registerUpdated();

        $this->isAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        $this->assertEquals('US', $user->country);
        $this->assertEquals('en', $user->language);
        $this->assertEquals('10010', $user->addr_zip);
        $this->assertEquals('CA', $user->addr_state);

        // The user should not have any `feature_flags`.
        $this->assertEquals(true, is_null($user->feature_flags));
    }

    /**
     * Test that users can't enter invalid profile info.
     */
    public function testRegisterBetaInvalid()
    {
        $this->withHeader('X-Fastly-Country-Code', 'US');

        $response = $this->post('/register', [
            'first_name' => $this->faker->text(150),
            'email' => $this->faker->unique->email,
            'password' => '123',
        ]);

        $response->assertSessionHasErrors([
            'first_name' =>
                'The first name may not be greater than 50 characters.',
        ]);

        $this->assertGuest('web');
    }

    /**
     * Test that users can register from other countries
     * and get the correct `country` and `language` fields.
     */
    public function testRegisterFromMexico()
    {
        $this->withHeader('X-Fastly-Country-Code', 'MX')->registerUpdated();

        $this->isAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        $this->assertEquals('MX', $user->country);
        $this->assertEquals('es-mx', $user->language);
    }

    /**
     * Test that users can't brute-force the login form.
     */
    public function testRegisterBetaRateLimited()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->registerUpdated();
            $this->isAuthenticated('web');
        }

        $this->expectsEvents(\App\Events\Throttled::class);

        $this->registerUpdated()->assertSessionHas(
            'flash',
            'Too many attempts. Please try again in 15 minutes.',
        );

        $this->assertGuest('web');
    }

    /*
     * Test that the various optional variables for customizing the experience
     * display on the page.
     */
    // @TODO Remove or update post NS flow launch!
    public function testAuthorizeSessionVariablesExist()
    {
        $client = factory(Client::class)->states('authorization_code')->create();

        $response = $this->get(
            'authorize?' .
                http_build_query([
                    'response_type' => 'code',
                    'client_id' => $client->client_id,
                    'client_secret' => $client->client_secret,
                    'scope' => 'user',
                    'state' => csrf_token(),
                    'title' => 'test title',
                    'callToAction' => 'test call to action',
                ]),
        );

        $response->assertRedirect('/register');
        $response->assertSessionHasAll([
            'title' => 'test title',
            'callToAction' => 'test call to action',
        ]);
    }
}
