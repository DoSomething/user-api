<?php

use Northstar\Models\User;
use Laravel\Socialite\AbstractUser;

class GoogleTest extends BrowserKitTestCase
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
     * Make all of the fields to fake a Socialite user.
     * TODO: Add birthdate.
     *
     * @param  string  $email      email
     * @param  string  $first_name first name
     * @param  string  $last_name  last name
     * @param  string  $id         id
     * @param  string  $token      token
     * @return \Laravel\Socialite\Two\User
     */
    private function mockSocialiteAbstractUser($email, $first_name, $last_name, $id, $token)
    {
        $fields = compact('id', 'email', 'token');

        $user = new Laravel\Socialite\Two\User();
        $user->map($fields);

        $user->user['given_name'] = $first_name;
        $user->user['family_name'] = $last_name;

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
    }

    /**
     * Test that a user is redirected to Google.
     */
    public function testGoogleRedirect()
    {
        // $this->visit('/google/continue');
        // @TODO: Test below results in a 404: "A request to [https://accounts.google.com/o/oauth2/auth?client_id=....M9] failed. Received status code [404]."
        // $this->assertRedirectedTo('https://accounts.google.com');
    }

    /**
     * Test a brand new user connecting through Google will
     * successfully get logged in with an account.
     */
    public function testGoogleVerify()
    {
        $this->defaultMock();

        $this->visit('/google/verify')->seePageIs('/');
        $this->seeIsAuthenticated('web');

        $user = auth()->user();
        $this->assertEquals($user->email, 'test@dosomething.org');
        $this->assertEquals($user->source, 'northstar');
        $this->assertEquals($user->source_detail, 'google');
        $this->assertEquals($user->email_subscription_status, true);
        $this->assertEquals($user->email_subscription_topics, ['community']);
    }

    /**
     * Test that public profile fields such as names, email and Facebook ID
     * are configured.
     */
    public function testGooglePublicProfileFieldsAreSet()
    {
        $this->defaultMock();

        $this->visit('/google/verify')->seePageIs('/');
        $this->seeIsAuthenticated('web');

        $user = auth()->user();
        $this->assertEquals($user->email, 'test@dosomething.org');
        $this->assertEquals($user->first_name, 'Puppet');
        $this->assertEquals($user->last_name, 'Sloth');
        $this->assertEquals($user->google_id, '12345');
        $this->assertEquals($user->email_subscription_status, true);
        $this->assertEquals($user->email_subscription_topics, ['community']);
    }

    /**
     * Test that an existing Northstar account can successfully login and merge
     * with a Google user profile.
     */
    public function testGoogleAccountMerge()
    {
        $factoryUser = factory(User::class)->create([
            'email' => 'test@dosomething.org',
            'first_name' => 'Joe',
            'last_name' => null,
        ]);

        $this->defaultMock();

        $this->visit('/google/verify');

        $user = auth()->user();
        $this->assertEquals($user->first_name, 'Puppet');
        $this->assertEquals($user->last_name, 'Sloth');
    }

    /**
     * If the user does not share email, it should not authenticate them.
     */
    public function testMissingEmail()
    {
        $abstractUser = $this->mockSocialiteAbstractUser('', 'Puppet', 'Sloth', '12345', 'token');
        $this->mockSocialiteFromUser($abstractUser);

        $this->visit('/google/verify')
            ->seePageIs('/register')
            ->see('We need your email');
        $this->dontSeeIsAuthenticated('web');
    }
}
