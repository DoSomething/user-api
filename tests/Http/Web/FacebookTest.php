<?php

class FacebookTest extends TestCase
{
    /**
     * Mock a Socialite user.
     *
     * @param  string  $email email
     * @param  string  $token token
     * @param  int     $id    id
     */
    private function mockSocialiteFacade($email, $name, $id)
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');

        $user = new Laravel\Socialite\Two\User();
        $user->map(compact('id', 'name', 'email'));

        Socialite::shouldReceive('driver->user')->andReturn($user);
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
        $this->mockSocialiteFacade('test@dosomething.org', 'Joe', 12345);

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
        $this->mockSocialiteFacade('test@dosomething.org', 'Puppet Sloth', 12345);

        $this->visit('/facebook/verify')->seePageIs('/');
        $this->seeIsAuthenticated('web');

        $user = auth()->user();
        $this->assertEquals($user->first_name, 'Puppet');
    }
}
