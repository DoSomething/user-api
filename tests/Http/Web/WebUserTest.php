<?php

namespace Tests\Http\Web;

use App\Models\User;
use Tests\TestCase;

class WebUserTest extends TestCase
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

        $response = $this->get('users/' . $user->id);

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /**
     * Test that users are provided a button to edit their profile from the homepage.
     */
    public function testSeeProfileEditForm()
    {
        $user = $this->makeAuthWebUser();

        $userEditUrl = 'users/' . $user->id . '/edit';

        $stepOneResponse = $this->get('/');

        $stepOneResponse->assertStatus(200);
        $stepOneResponse->assertSee(
            '<a href="' .
                url($userEditUrl) .
                '" class="button -secondary">Edit Profile</a>',
            false,
        );
    }

    /**
     * Test that users are provided a cancel button on the edit profile page.
     */
    public function testCancelProfileEdit()
    {
        $user = $this->makeAuthWebUser();

        $userUrl = 'users/' . $user->id;

        $response = $this->get($userUrl . '/edit');

        $response->assertStatus(200);
        $response->assertSee(
            '<a href="' . url($userUrl) . '">Cancel</a>',
            false,
        );
    }

    /**
     * Test that users can edit their profile.
     */
    public function testProfileEdit()
    {
        $user = $this->makeAuthWebUser();

        $this->patch('users/' . $user->id, [
            'first_name' => 'Jean-Paul',
            'last_name' => 'Beaubier-Lee',
            'birthdate' => $user->birthdate,
            'mobile' => $user->mobile,
            'sms_status' => $user->sms_status,
        ]);

        $updatedUser = User::find($user->id);

        $this->assertEquals('Jean-Paul', $updatedUser->first_name);
        $this->assertEquals('Beaubier-Lee', $updatedUser->last_name);
    }

    /**
     * Test that auth user can not access another user's profile.
     */
    public function testProfileEditAccess()
    {
        $this->makeAuthWebUser();

        $randoUser = factory(User::class)->create();

        $response = $this->get('users/' . $randoUser->id . '/edit');

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }
}
