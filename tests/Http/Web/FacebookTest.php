<?php

class FacebookTest extends TestCase
{
    /**
     * Mock a Socialite user for the given
     * method and user fields.
     *
     * @param  array  $fields
     * @param  string $method
     */
    private function mockSocialiteFacade($fields, $method)
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');

        $user = new Laravel\Socialite\Two\User();
        $user->map($fields);

        Socialite::shouldReceive($method)->andReturn($user);
    }

    /**
     * Mock a Socialite user.
     *
     * @param  string  $email email
     * @param  string  $token token
     * @param  string  $id    id
     * @param  string  $token token
     */
    private function mockSocialiteFromUser($email, $name, $id, $token)
    {
        $this->mockSocialiteFacade(compact('id', 'name', 'email', 'token'), 'driver->user');
    }

    /**
     * Mock a Socialite user being requested from a Token.
     *
     * @param  string  $email email
     * @param  string  $token token
     * @param  string  $id    id
     * @param  string  $token token
     */
    private function mockSocialiteFromUserToken($email, $name, $id, $token)
    {
        $this->mockSocialiteFacade(compact('id', 'name', 'email', 'token'), 'driver->userFromToken');
    }

    /**
     * Test that a user is redirected to Facebook
     * @expectedException \Illuminate\Foundation\Testing\HttpException
     * @expectedExceptionMessageRegExp /www\.facebook\.com/
     */
    public function testFacebookRedirect()
    {
        $this->visit('/facebook/continue');
        $this->assertRedirectedTo('https://www.facebook.com/');
    }

    /**
     * Test a brand new user connecting through Facebook will
     * successfully get logged in with an account.
     */
    public function testFacebookVerify()
    {
        $this->mockSocialiteFromUser('test@dosomething.org', 'Joe', '12345', 'token');
        $this->mockSocialiteFromUserToken('test@dosomething.org', 'Joe', '12345', 'token');

        $this->visit('/facebook/verify')->seePageIs('/');
        $this->seeIsAuthenticated('web');

        $user = auth()->user();
        $this->assertEquals($user->email, 'test@dosomething.org');
    }

    /**
     * Test that a full name is split into just a first name.
     */
    public function testFacebookNameSplit()
    {
        $this->mockSocialiteFromUser('test@dosomething.org', 'Puppet Sloth', '12345', 'token');
        $this->mockSocialiteFromUserToken('test@dosomething.org', 'Puppet Sloth', '12345', 'token');

        $this->visit('/facebook/verify')->seePageIs('/');
        $this->seeIsAuthenticated('web');

        $user = auth()->user();
        $this->assertEquals($user->first_name, 'Puppet');
        $this->assertEquals($user->last_name, 'Sloth');
    }

    /**
     * Test that an invalid token will return a bad response
     * and the user will not be logged in.
     */
    public function testFacebookTokenValidation()
    {
        $this->mockSocialiteFromUser('test@dosomething.org', 'Puppet Sloth', '12345', 'token');
        Socialite::shouldReceive('driver->userFromToken')->andReturnUsing(function () {
            $request = new GuzzleHttp\Psr7\Request('GET', 'http://graph.facebook.com');
            throw new GuzzleHttp\Exception\RequestException('Token validation failed', $request);
        });

        $this->visit('/facebook/verify')
            ->seePageIs('/login')
            ->see('Unable to verify Facebook account.');
        $this->dontSeeIsAuthenticated('web');
    }
}
