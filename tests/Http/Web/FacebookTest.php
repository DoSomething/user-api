<?php

use Northstar\Models\User;
use Laravel\Socialite\AbstractUser;

class FacebookTest extends BrowserKitTestCase
{
    /**
     * Mock a Socialite user for the given
     * method and user fields.
     *
     * @param  AbstractUser  $user
     * @param  string        $method
     */
    private function mockSocialiteFacade($user, $method)
    {
        Socialite::shouldReceive($method)->andReturn($user);
    }

    /**
     * Mock Socialite driver->user method with the given user.
     *
     * @param  AbstractUser  $user
     */
    private function mockSocialiteFromUser($user)
    {
        $this->mockSocialiteFacade($user, 'driver->user');
    }

    /**
     * Mock Socialite driver->fields->userFromToken method with
     * the given user.
     *
     * @param  AbstractUser  $user
     */
    private function mockSocialiteFromUserToken($user)
    {
        $this->mockSocialiteFacade($user, 'driver->fields->userFromToken');
    }

    /**
     * Make all of the fields to fake a Socialite user.
     *
     * @param  string  $email      email
     * @param  string  $first_name first name
     * @param  string  $last_name  last name
     * @param  string  $id         id
     * @param  string  $token      token
     * @param  string  $birthday   birthday
     * @return \Laravel\Socialite\Two\User
     */
    private function mockSocialiteAbstractUser($email, $first_name, $last_name, $id, $token, $birthday = null)
    {
        $fields = compact('id', 'email', 'token');

        $user = new Laravel\Socialite\Two\User();
        $user->map($fields);

        $user->user['first_name'] = $first_name;
        $user->user['last_name'] = $last_name;

        if (! is_null($birthday)) {
            $user->user['birthday'] = $birthday;
        }

        return $user;
    }

    /**
     * Default set of operations that need to happen for most
     * of the tests.
     */
    private function defaultMock()
    {
        $abstractUser = $this->mockSocialiteAbstractUser('test@dosomething.org', 'Puppet', 'Sloth', '12345', 'token');
        $this->mockSocialiteFromUser($abstractUser);
        $this->mockSocialiteFromUserToken($abstractUser);
    }

    /**
     * Test that a user is redirected to Facebook
     * @expectedException \Laravel\BrowserKitTesting\HttpException
     * @expectedExceptionMessageRegExp /www\.facebook\.com/
     */
    public function testFacebookRedirect()
    {
        // @TODO: Why do we need these two magic annotations?
        $this->visit('/facebook/continue');
        $this->assertRedirectedTo('https://www.facebook.com/');
    }

    /**
     * Test a brand new user connecting through Facebook will
     * successfully get logged in with an account.
     */
    public function testFacebookVerify()
    {
        $this->defaultMock();
        // Turn on the badges and refer-friends-scholarship test feature flags.
        config([
            'features.badges' => true,
            'features.refer-friends-scholarship' => true,
        ]);

        $this->visit('/facebook/verify')->seePageIs('/profile/about');
        $this->seeIsAuthenticated('web');

        $user = auth()->user();
        $this->assertEquals($user->email, 'test@dosomething.org');
        $this->assertEquals($user->source, 'northstar');
        $this->assertEquals($user->source_detail, 'facebook');
        $this->assertEquals($user->country_code, country_code());
        $this->assertEquals($user->language, app()->getLocale());
        $this->assertEquals($user->email_subscription_status, true);
        $this->assertEquals($user->email_subscription_topics, ['community']);
        $this->assertArrayHasKey('badges', $user->feature_flags);
        $this->assertEquals(true, $user->feature_flags['refer-friends-scholarship']);
    }

    /**
     * Test that public profile fields such as names, email and Facebook ID
     * are configured.
     */
    public function testFacebookPublicProfileFieldsAreSet()
    {
        $this->defaultMock();

        $this->visit('/facebook/verify')->seePageIs('/profile/about');
        $this->seeIsAuthenticated('web');

        $user = auth()->user();
        $this->assertEquals($user->email, 'test@dosomething.org');
        $this->assertEquals($user->first_name, 'Puppet');
        $this->assertEquals($user->last_name, 'Sloth');
        $this->assertEquals($user->facebook_id, '12345');
        $this->assertEquals($user->email_subscription_status, true);
        $this->assertEquals($user->email_subscription_topics, ['community']);
    }

    /**
     * Test that an invalid token will return a bad response
     * and the user will not be logged in.
     */
    public function testFacebookTokenValidation()
    {
        $this->mockSocialiteFromUser($this->mockSocialiteAbstractUser('test@dosomething.org', 'Puppet', 'Sloth', '12345', 'token'));

        Socialite::shouldReceive('driver->fields->userFromToken')->andReturnUsing(function () {
            $request = new GuzzleHttp\Psr7\Request('GET', 'http://graph.facebook.com');
            throw new GuzzleHttp\Exception\RequestException('Token validation failed', $request);
        });

        $this->visit('/facebook/verify')
            ->seePageIs('/register')
            ->see('Unable to verify Facebook account.');
        $this->dontSeeIsAuthenticated('web');
    }

    /**
     * Test that an existing Northstar account can successfully login and merge
     * with a Facebook user profile.
     */
    public function testFacebookAccountMerge()
    {
        $factoryUser = factory(User::class)->create([
            'email' => 'test@dosomething.org',
            'first_name' => 'Joe',
            'last_name' => null,
        ]);

        $this->defaultMock();

        $this->visit('/facebook/verify')->seePageIs('/');

        $user = auth()->user();
        $this->assertEquals($user->first_name, 'Joe');
        $this->assertEquals($user->last_name, 'Sloth');
        $this->assertEquals($user->birthdate, $factoryUser->birthdate);
    }

    /**
     * If the user hides the birthday, make sure we discard it.
     */
    public function testFacebookPartialBirthday()
    {
        $abstractUser = $this->mockSocialiteAbstractUser('test@dosomething.org', 'Puppet', 'Sloth', '12345', 'token', '01/01');
        $this->mockSocialiteFromUser($abstractUser);
        $this->mockSocialiteFromUserToken($abstractUser);

        $this->visit('/facebook/verify');

        $user = auth()->user();
        $this->assertNull($user->birthdate);
    }

    /**
     * If the user lets us see a full birthday, check we format it correctly.
     */
    public function testFullBirthday()
    {
        $abstractUser = $this->mockSocialiteAbstractUser('test@dosomething.org', 'Puppet', 'Sloth', '12345', 'token', '01/01/2000');
        $this->mockSocialiteFromUser($abstractUser);
        $this->mockSocialiteFromUserToken($abstractUser);

        $this->visit('/facebook/verify');

        $user = auth()->user();
        $this->assertEquals($user->birthdate, new Carbon\Carbon('2000-01-01'));
    }

    /**
     * If the user does not share email, it should not authenticate them.
     */
    public function testMissingEmail()
    {
        $abstractUser = $this->mockSocialiteAbstractUser('', 'Puppet', 'Sloth', '12345', 'token', '01/01/2000');
        $this->mockSocialiteFromUser($abstractUser);
        $this->mockSocialiteFromUserToken($abstractUser);

        $this->visit('/facebook/verify')
            ->seePageIs('/register')
            ->see('We need your email');
        $this->dontSeeIsAuthenticated('web');
    }
}
