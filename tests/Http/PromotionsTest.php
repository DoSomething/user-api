<?php

use App\Models\User;

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

        $this->asAdminUser()->delete(
            'v2/users/' . $user->id . '/promotions',
        );

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
}
