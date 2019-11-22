<?php

use Northstar\Models\User;
use Northstar\Services\Google;
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
     * @see https://developers.google.com/people/api/rest/v1/people/get
     * @return object
     */
    private function mockGoogleProfile($googleId)
    {
        return (object) [
            'resourceName' => 'people/'.$googleId,
            'birthdays' => [
                (object) [
                    'metadata' => (object) [
                        'source' => (object) [
                            'type' => 'DOMAIN_PROFILE',
                            'id' => $googleId,
                        ],
                    ],
                    'date' => (object) [
                        'month' => 7,
                        'day' => 11,
                    ],
                ],
                (object) [
                    'metadata' => (object) [
                        'source' => (object) [
                            'type' => 'ACCOUNT',
                            'id' => $googleId,
                        ],
                    ],
                    'date' => (object) [
                        'year' => 2001,
                        'month' => 7,
                        'day' => 11,
                    ],
                ],
            ],
        ];
    }

    /**
     * Default set of operations that need to happen for most
     * of the tests.
     */
    private function defaultMock()
    {
        $googleId = '12345';
        $abstractUser = $this->mockSocialiteAbstractUser('test@dosomething.org', 'Puppet', 'Sloth', $googleId, 'token');
        $this->mockSocialiteFromUser($abstractUser);
        $this->mock(Google::class)
          ->shouldReceive('getProfile')
          ->andReturn($this->mockGoogleProfile($googleId));
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
        // Turn on the badges and refer-friends-scholarship test feature flags.
        config([
            'features.badges' => true,
            'features.refer-friends-scholarship' => true,
        ]);

        $this->visit('/google/verify')->seePageIs('/profile/about');
        $this->seeIsAuthenticated('web');

        $user = auth()->user();
        $this->assertEquals($user->email, 'test@dosomething.org');
        $this->assertEquals($user->source, 'northstar');
        $this->assertEquals($user->source_detail, 'google');
        $this->assertEquals($user->country_code, country_code());
        $this->assertEquals($user->language, app()->getLocale());
        $this->assertEquals($user->email_subscription_status, true);
        $this->assertEquals($user->email_subscription_topics, ['community']);
        $this->assertEquals($user->birthdate, new Carbon\Carbon('2001-07-11'));
        $this->assertArrayHasKey('badges', $user->feature_flags);
        $this->assertEquals(true, $user->feature_flags['refer-friends-scholarship']);
    }

    /**
     * Test that public profile fields such as names, email and Google ID
     * are configured.
     */
    public function testGooglePublicProfileFieldsAreSet()
    {
        $this->defaultMock();

        $this->visit('/google/verify')->seePageIs('/profile/about');
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
     * Test that attempted registrations with missing required profile fields are
     * unsuccessful and the correct flash message is presented.
     */
    public function testGoogleMissingProfileFields()
    {
        $googleId = '12345';
        $abstractUser = $this->mockSocialiteAbstractUser('test@dosomething.org', null, null, $googleId, 'token');
        $this->mockSocialiteFromUser($abstractUser);
        $this->mock(Google::class)
            ->shouldReceive('getProfile')
            ->andReturn($this->mockGoogleProfile($googleId));

        $this->visit('/google/verify')->seePageIs('/register');
        $this->see('We need your first and last name to create your account! Please confirm that these are set on your Google profile and try again.');
    }

    /**
     * Test that authentication is still successful when the 'birthdays' field is missing
     * from the Google Profile.
     */
    public function testGoogleMissingBirthday()
    {
        $googleId = '12345';
        $abstractUser = $this->mockSocialiteAbstractUser('test@dosomething.org', 'Puppet', 'Sloth', $googleId, 'token');
        $this->mockSocialiteFromUser($abstractUser);

        $mockGoogleProfile = $this->mockGoogleProfile($googleId);
        // Remove the birthdays attribute from the mocked Google Profile payload.
        unset($mockGoogleProfile->birthdays);

        $this->mock(Google::class)
            ->shouldReceive('getProfile')
            ->andReturn($mockGoogleProfile);

        $this->visit('/google/verify')->seePageIs('/profile/about');
        $this->seeIsAuthenticated('web');
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

        $this->visit('/google/verify')->seePageIs('/');

        $user = auth()->user();
        $this->assertEquals($user->first_name, 'Joe');
        $this->assertEquals($user->last_name, 'Sloth');
        $this->assertEquals($user->birthdate, $factoryUser->birthdate);
    }
}
