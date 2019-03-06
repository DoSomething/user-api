<?php

use Northstar\Models\User;
use Northstar\PasswordResetType;

class ResetTest extends BrowserKitTestCase
{
    /**
     * Test that anonymous and non-admin keys/users cannot create
     * password reset links.
     * POST /resets
     *
     * @test
     */
    public function testResetNotAccessibleByNonAdmin()
    {
        $this->post('v2/resets');
        $this->assertResponseStatus(401);

        $this->asNormalUser()->post('v2/resets');
        $this->assertResponseStatus(401);

        $this->asStaffUser()->post('v2/resets');
        $this->assertResponseStatus(401);
    }

    /**
     * Test creating a new password reset link.
     * POST /resets
     *
     * @test
     */
    public function testCreatePasswordResetLink()
    {
        config(['features.blink' => true]);
        $user = factory(User::class)->create();

        $this->asAdminUser()->post('v2/resets', [
            'id' => $user->id,
            'type' => 'forgot-password',
        ]);
        $this->assertResponseStatus(200);
        $this->seeJsonStructure(['success']);
        $this->blinkMock->shouldHaveReceived('userPasswordReset')->once();

        $this->seeInDatabase('password_resets', ['email' => $user->email]);
    }

    /**
     * Test creating a new password reset link requires write scope.
     * POST /resets
     *
     * @test
     */
    public function testCreatePasswordResetLinkRequiresWriteScope()
    {
        $admin = factory(User::class, 'admin')->create();
        $user = factory(User::class)->create();

        $response = $this->asUser($admin, ['role:admin', 'user'])->post('v2/resets', [
            'id' => $user->id,
            'type' => PasswordResetType::$forgotPassword,
        ]);

        $this->assertResponseStatus(401);
        $this->assertEquals('Requires the `write` scope.', $response->decodeResponseJson()['hint']);
    }
}
