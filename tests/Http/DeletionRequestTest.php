<?php

use Carbon\Carbon;
use Northstar\Models\User;

class DeletionRequestTest extends BrowserKitTestCase
{
    /**
     * Test that a user can mark themselves for deletion.
     * POST /v2/users/:id/deletion
     *
     * @return void
     */
    public function testMarkingSelfForDeletion()
    {
        $this->mockTime('April 26 2019 7:00pm');

        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->post('v2/users/'.$user->id.'/deletion');

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.deletion_requested_at', '2019-04-26T19:00:00+00:00');
    }

    /**
     * Test that a user can un-mark themselves for deletion.
     * DELETE /v2/users/:id/deletion
     *
     * @return void
     */
    public function testUnmarkingSelfForDeletion()
    {
        $user = factory(User::class)->create([
            'deletion_requested_at' => new Carbon('2019-04-26T19:00:00+00:00'),
        ]);

        $this->asUser($user, ['user', 'write'])->delete('v2/users/'.$user->id.'/deletion');

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.deletion_requested_at', null);
    }

    /**
     * Test that a staffer can mark users for deletion.
     * DELETE /v2/users/:id/deletion
     *
     * @return void
     */
    public function testStaffCanMarkUsersForDeletion()
    {
        $this->mockTime('April 26 2019 7:00pm');

        $staffer = factory(User::class)->create(['role' => 'staff']);
        $user = factory(User::class)->create();

        $this->asUser($staffer, ['user', 'write'])->post('v2/users/'.$user->id.'/deletion');

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.deletion_requested_at', '2019-04-26T19:00:00+00:00');
    }

    /**
     * Test that a normal user can't delete someone else.
     * DELETE /v2/users/:id/deletion
     *
     * @return void
     */
    public function testNormalUsersCantMarkOthersForDeletion()
    {
        $villain = factory(User::class)->create();
        $user = factory(User::class)->create();

        $this->asUser($villain, ['user', 'write'])->delete('v2/users/'.$user->id.'/deletion');

        $this->assertResponseStatus(403);
    }
}
