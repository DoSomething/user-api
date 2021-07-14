<?php

namespace Tests\Http;

use App\Models\User;
use Tests\BrowserKitTestCase;

class PromotionsTest extends BrowserKitTestCase
{
    /**
     * Test that an admin can mute promotions.
     *
     * @return void
     */
    public function testAdminCanMutePromotions()
    {
        $user = factory(User::class)->create();

        $this->asAdminUser()->delete('v2/users/' . $user->id . '/promotions');

        $this->assertResponseStatus(200);
        $this->assertNotNull($user->fresh()->promotions_muted_at);
    }

    /**
     * Test that a non-staff user can't mute promotions.
     *
     * @return void
     */
    public function testNormalUserCannotMutePromotions()
    {
        $villain = factory(User::class)->create();
        $user = factory(User::class)->create();

        $this->asUser($villain, ['user', 'write'])->delete(
            'v2/users/' . $user->id . '/promotions',
        );

        $this->assertResponseStatus(401);
    }

    /**
     * Test muting promotions on a deleted user.
     *
     * @return void
     */
    public function testCanMutePromotionsForDeletedUser()
    {
        $user = factory(User::class)->create();

        $user->delete();

        $this->asAdminUser()->delete('v2/users/' . $user->id . '/promotions');

        $this->assertResponseStatus(200);
        $this->assertNotNull($user->fresh()->promotions_muted_at);
    }

    /**
     * Test status when user not found.
     *
     * @return void
     */
    public function testStatusWhenMutePromotionsForNotFoundUser()
    {
        $this->asAdminUser()->delete(
            'v2/users/600201a023a8223a1e4575a3/promotions',
        );

        $this->assertResponseStatus(404);
    }
}
