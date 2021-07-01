<?php

namespace Tests\Http;

use App\Models\User;
use App\Types\PasswordResetType;
use Tests\BrowserKitTestCase;

class ResetTest extends BrowserKitTestCase
{
    /**
     * Test that anonymous and non-admin keys/users cannot create
     * password reset links.
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
     *
     * @test
     */
    public function testCreatePasswordResetLink()
    {
        $user = factory(User::class)->create();

        $this->asAdminUser()->post('v2/resets', [
            'id' => $user->id,
            'type' => 'forgot-password',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure(['success']);
        $this->customerIoMock->shouldHaveReceived('sendEmail')->once();
        $this->seeInMongoDatabase('password_resets', ['email' => $user->email]);
    }

    /**
     * Test creating a new password reset link requires write scope.
     *
     * @test
     */
    public function testCreatePasswordResetLinkRequiresWriteScope()
    {
        $admin = factory(User::class)
            ->states('admin')
            ->create();

        $user = factory(User::class)->create();

        $this->asUser($admin, ['role:admin', 'user'])->post('v2/resets', [
            'id' => $user->id,
            'type' => PasswordResetType::get('FORGOT_PASSWORD'),
        ]);

        $this->assertResponseStatus(401);
        $this->assertEquals(
            'Requires the `write` scope.',
            $this->response->decodeResponseJson()['hint'],
        );
    }
}
