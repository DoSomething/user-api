<?php

use App\Models\User;

class WebUserTest extends BrowserKitTestCase
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
     * Test that an authenticated, unauthorized user cannot see the
     * /users/:id page and is redirected to view their profile on
     * the homepage.
     */
    public function testProfileShowRedirect()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('users/' . $user->id)->seePageIs('/');
    }

    /**
     * Test that users can click to edit their profile from the homepage.
     */
    public function testSeeProfileEditForm()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/')
            ->click('Edit Profile')
            ->seePageIs('users/' . $user->id . '/edit');
    }

    /**
     * Test that users can cancel out of editing their profile and head
     * back to the homepage.
     */
    public function testCancelProfileEdit()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('users/' . $user->id . '/edit')
            ->click('Cancel')
            ->seePageIs('/');
    }

    /**
     * Test that users can edit their profile.
     */
    public function testProfileEdit()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('users/' . $user->id . '/edit')
            ->type('Jean-Paul', 'first_name')
            ->type('Beaubier-Lee', 'last_name')
            ->press('Save')
            ->seePageIs('/');

        $updatedUser = User::find($user->id);

        $this->assertEquals('Jean-Paul', $updatedUser->first_name);
        $this->assertEquals('Beaubier-Lee', $updatedUser->last_name);
    }

    /**
     * Test that auth user can not access another user's profile.
     */
    public function testProfileEditAccess()
    {
        $authUser = $this->makeAuthWebUser();

        $randoUser = factory(User::class)->create();

        $this->visit('users/' . $randoUser->id . '/edit')->seePageIs('/');
    }
}
