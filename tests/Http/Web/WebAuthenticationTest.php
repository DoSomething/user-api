<?php

use Northstar\Models\User;
use Northstar\Models\Client;

class WebAuthenticationTest extends BrowserKitTestCase
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
        $this->get('/')->followRedirects();

        $this->seePageIs('register');
    }

    /**
     * Test that the profile renders for logged-in users.
     */
    public function testHomepage()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'web');

        $this->visit('/')
            ->followRedirects()
            ->see('You are logged in as');

        $this->assertResponseOk();
    }

    /**
     * Test that users can log in via the web.
     */
    public function testLogin()
    {
        $user = factory(User::class)->create([
            'email' => 'login-test@dosomething.org',
            'password' => 'secret',
        ]);

        $this->expectsEvents(\Illuminate\Auth\Events\Login::class);

        $this->visit('login')
            ->type('Login-Test@dosomething.org', 'username')
            ->type('secret', 'password')
            ->press('Log In');

        $this->seeIsAuthenticatedAs($user, 'web');
    }

    /**
     * Test that users cannot login with bad credentials via the web.
     */
    public function testLoginWithInvalidCredentials()
    {
        factory(User::class)->create(['email' => 'login-test@dosomething.org', 'password' => 'secret']);

        $this->expectsEvents(\Illuminate\Auth\Events\Failed::class);

        $this->visit('login')
            ->type('Login-Test@dosomething.org', 'username')
            ->type('open-sesame', 'password') // <-- wrong password!
            ->press('Log In');

        $this->see('These credentials do not match our records.');
    }

    /**
     * Test that users can't brute-force the login form.
     */
    public function testLoginRateLimited()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->visit('login');
            $this->submitForm('Log In', [
                'username' => 'target@example.com',
                'password' => 'password'.$i,
            ]);

            $this->see('These credentials do not match our records.');
        }

        // This next request should trigger a StatHat counter.
        $this->expectsEvents(\Northstar\Events\Throttled::class);

        $this->visit('login');
        $this->submitForm('Log In', [
            'username' => 'target@example.com',
            'password' => 'password11', // our attacker is very methodical.
        ]);

        $this->see('Too many attempts.');
    }

    /**
     * Test that users who do not have a password on their account
     * are asked to reset it.
     */
    public function testLoginWithoutPasswordSet()
    {
        factory(User::class)->create(['email' => 'puppet-sloth@dosomething.org', 'password' => null]);

        // Puppet Sloth doesn't have a DS.org password yet, but he tries to enter
        // "next-question" because that's his password everywhere else.
        $this->visit('login')->submitForm('Log In', [
            'username' => 'puppet-sloth@dosomething.org',
            'password' => 'next-question',
        ]);

        $this->seeText('You need to reset your password before you can log in.');
    }

    /**
     * Test that an authenticated user can log out.
     */
    public function testLogout()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'web');

        $this->get('logout')->followRedirects();

        $this->seePageIs('login');
        $this->dontSeeIsAuthenticated('web');
    }

    /**
     * Test that we can specify a custom post-logout redirect.
     */
    public function testLogoutRedirect()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'web');

        $this->get('logout?redirect=http://dev.dosomething.org:8888');

        $this->dontSeeIsAuthenticated('web');
        $this->assertResponseStatus(302);
        $this->seeHeader('Location', 'http://dev.dosomething.org:8888');
    }

    /**
     * Test that we can't be redirected to a third party domain
     * in the custom post-logout redirect.
     */
    public function testLogoutRedirectThirdPartyDomain()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'web');

        $this->get('logout?redirect=http://dosomething.org.sloth.com');

        $this->dontSeeIsAuthenticated('web');
        $this->assertResponseStatus(302);

        $location = $this->response->headers->get('Location');
        $this->assertNotEquals('http://dosomething.org.sloth.com', $location);
    }

    /**
     * Test that users can register via the web.
     */
    public function testRegister()
    {
        $this->withHeader('X-Fastly-Country-Code', 'US')
            ->register();

        $this->seeIsAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        $this->assertEquals('US', $user->country);
        $this->assertEquals('en', $user->language);

        // The user should be signed up for email messaging.
        $this->assertEquals(true, $user->email_subscription_status);
        $this->assertEquals(['community'], $user->email_subscription_topics);
    }

    /**
     * Test that users get feature_flags values when tests are on.
     */
    public function testRegisterWithFeatureFlagsTest()
    {
        // Turn on the badges and refer-friends test feature flags.
        config([
            'features.badges' => true,
            'features.refer-friends' => true,
        ]);

        $this->withHeader('X-Fastly-Country-Code', 'US')
            ->register();

        $this->seeIsAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        // The user should have a value set for 'badges'
        $this->assertArrayHasKey('badges', $user->feature_flags);
        // The user should have true set for 'refer-friends'
        $this->assertEquals(true, $user->feature_flags['refer-friends']);
    }

    /**
     * Test that club referrals do not get feature flags set when the badges and refer-friends
     * tests are on.
     */
    public function testRegisterFromClubsWithoutFeatureFlagsTest()
    {
        // Turn on badges and refer-friends feature flags.
        config([
            'features.badges' => true,
            'features.refer-friends' => true,
        ]);

        // Mock a session for the user with a ?utm_source=clubs param, indicating a clubs referral
        $this->withSession(['source_detail' => ['utm_source' => 'clubs']])
            ->withHeader('X-Fastly-Country-Code', 'US')
            ->register();

        $this->seeIsAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        $this->assertEquals('US', $user->country);
        $this->assertEquals('en', $user->language);

        // The user should not have any `feature_flags`.
        $this->assertEquals(true, is_null($user->feature_flags));
    }

    /**
     * Test that users get refer-friends feature_flag value when test is on, and no badges
     * value when test is off.
     */
    public function testRegisterWithReferFriendsAndNoBadgesTest()
    {
        // Turn off badges, turn on refer-friends test feature flags.
        config([
            'features.badges' => false,
            'features.refer-friends' => true,
        ]);

        $this->withHeader('X-Fastly-Country-Code', 'US')
            ->register();

        $this->seeIsAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        // The user should not have a value set for 'badges'
        $this->assertArrayNotHasKey('badges', $user->feature_flags);
        // The user should have true set for 'refer-friends'
        $this->assertEquals(true, $user->feature_flags['refer-friends']);
    }

    /**
     * Test that users get badges feature_flag value when test is on, and no refer-friends
     * value when test is off.
     */
    public function testRegisterWithBadgesAndNoReferFriendsTest()
    {
        // Turn on badges, turn off refer-friends test feature flags.
        config([
            'features.badges' => true,
            'features.refer-friends' => false,
        ]);

        $this->withHeader('X-Fastly-Country-Code', 'US')
            ->register();

        $this->seeIsAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        // The user should have a value set for 'badges'
        $this->assertArrayHasKey('badges', $user->feature_flags);
        // The user should not have a value set for 'refer-friends'
        $this->assertArrayNotHasKey('refer-friends', $user->feature_flags);
    }

    /**
     * Test that users do not get feature flags set when the badges and refer-friends
     * tests are off.
     */
    public function testRegisterWithoutFeatureFlagTests()
    {
        // Turn off the badges and refer-friends test feature flags.
        config([
            'features.badges' => false,
            'features.refer-friends' => false,
        ]);

        $this->withHeader('X-Fastly-Country-Code', 'US')
            ->register();

        $this->seeIsAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        $this->assertEquals('US', $user->country);
        $this->assertEquals('en', $user->language);

        // The user should not have any `feature_flags`.
        $this->assertEquals(true, is_null($user->feature_flags));
    }

    /**
     * Test that users can't enter invalid profile info.
     */
    public function testRegisterInvalid()
    {
        $this->withHeader('X-Fastly-Country-Code', 'US');

        $this->visit('register');
        $this->submitForm('register-submit', [
            'first_name' => $this->faker->text(150),
            'email' => $this->faker->unique->email,
            'birthdate' => '1/15/2130',
            'password' => 'secret',
        ]);

        $this->see('The first name may not be greater than 50 characters');
        $this->see('The birthdate must be a date before now');
        $this->see('The voter registration status field is required');

        $this->dontSeeIsAuthenticated('web');
    }

    /**
     * Test that users can register from other countries
     * and get the correct `country` and `language` fields.
     */
    public function testRegisterFromMexico()
    {
        $this->withHeader('X-Fastly-Country-Code', 'MX')
            ->register();

        $this->seeIsAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        $this->assertEquals('MX', $user->country);
        $this->assertEquals('es-mx', $user->language);
    }

    /**
     * Test that users can't brute-force the login form.
     */
    public function testRegisterRateLimited()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->register();
            $this->seeIsAuthenticated('web');
        }

        $this->register();

        $this->dontSeeIsAuthenticated('web');
        $this->see('Too many attempts.');
    }

    /**
     * Test that the various optional variables for customizing the experience
     * display on the page.
     */
    public function testAuthorizeSessionVariablesExist()
    {
        $client = factory(Client::class, 'authorization_code')->create();

        $this->get('authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'scope' => 'user',
            'state' => csrf_token(),
            'title' => 'test title',
            'callToAction' => 'test call to action',
        ]))->followRedirects();

        $this->seePageIs('register')
            ->see('test title')
            ->see('test call to action');
    }
}
