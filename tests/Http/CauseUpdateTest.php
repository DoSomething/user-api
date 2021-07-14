<?php

namespace Tests\Http;

use App\Models\User;
use Tests\BrowserKitTestCase;

class CauseUpdateTest extends BrowserKitTestCase
{
    /**
     * Test that a user can add cause preferences.
     *
     * @return void
     */
    public function testAddCause()
    {
        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->post(
            'v2/users/' . $user->id . '/causes/animal_welfare',
        );

        $this->assertResponseStatus(200);

        $freshUser = $user->fresh();
        $this->assertContains('animal_welfare', $freshUser->causes);
    }

    /**
     * Test that a user cannot add a duplicate cause.
     *
     * @return void
     */
    public function testAddExistingCause()
    {
        $user = factory(User::class)->create([
            'causes' => ['bullying'],
        ]);

        $this->asUser($user, ['user', 'write'])->post(
            'v2/users/' . $user->id . '/causes/bullying',
        );

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.causes', ['bullying']);
    }

    /**
     * Test that a user cannot add a cause that does not exist.
     *
     * @return void
     */
    public function testAddNonExistentCause()
    {
        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->post(
            'v2/users/' . $user->id . '/causes/love_animals',
        );

        $this->assertResponseStatus(404);
    }

    /**
     * Test that a user can remove a cause.
     *
     * @return void
     */
    public function testRemoveCause()
    {
        $user = factory(User::class)->create([
            'causes' => ['gender_rights_equality', 'environment', 'bullying'],
        ]);

        $this->asUser($user, ['user', 'write'])->delete(
            'v2/users/' . $user->id . '/causes/gender_rights_equality',
        );

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.causes', ['environment', 'bullying']);
    }

    /**
     * Test that a user can remove a cause and it will return an empty array as expected.
     *
     * @return void
     */
    public function testRemoveFinalCause()
    {
        $user = factory(User::class)->create([
            'causes' => ['gender_rights_equality'],
        ]);

        $this->asUser($user, ['user', 'write'])->delete(
            'v2/users/' . $user->id . '/causes/gender_rights_equality',
        );

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.causes', []);
    }

    /**
     * Test that a user can't edit another user's causes.
     *
     * @return void
     */
    public function testNormalUsersCantChangeOthersCausePreferences()
    {
        $villain = factory(User::class)->create();
        $user = factory(User::class)->create();

        $this->asUser($villain, ['user', 'write'])->post(
            'v2/users/' . $user->id . '/causes/bullying',
        );

        $this->assertResponseStatus(403);
    }
}
