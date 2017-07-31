<?php

use Northstar\Models\User;
use Northstar\Models\Client;

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
     * Test that users can register via the web.
     */
    public function testRegister()
    {
        $this->phoenixMock->shouldReceive('sendTransactional')->once();
        $this->withHeader('X-Fastly-Country-Code', 'US')
            ->register();

        $this->seeIsAuthenticated('web');

        /** @var User $user */
        $user = auth()->user();

        $this->assertEquals('US', $user->country);
        $this->assertEquals('en', $user->language);
    }

    /**
     * Test that users can register from other countries
     * and get the correct `country` and `language` fields.
     */
    public function testRegisterFromMexico()
    {
        $this->phoenixMock->shouldReceive('sendTransactional')->once();
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
        $this->phoenixMock->shouldReceive('sendTransactional')->times(10);

        for ($i = 0; $i < 10; $i++) {
            $this->register();
            $this->seeIsAuthenticated('web');
        }

        $this->register();

        $this->dontSeeIsAuthenticated('web');
        $this->see('Too many attempts.');
    }

    public function testAuthorizeSessionVariablesExist()
    {
        $client = Client::create(['client_id' => 'phpunit', 'scope' => ['user'], 'redirect_uri' => 'http://example.com/']);

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
