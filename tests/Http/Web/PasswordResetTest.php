<?php

use App\Auth\Registrar;
use App\Jobs\SendPasswordResetToCustomerIo;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

class PasswordResetTest extends BrowserKitTestCase
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
     * Test that the homepage redirects to login page.
     */
    public function testForgotPasswordResetFlow()
    {
        Bus::fake();

        $user = factory(User::class)->create([
            'email' => 'forgetful@example.com',
        ]);
        $resetPasswordUrl = '';

        // The user should be able to request a new password by entering their email.
        $this->visit('/password/reset');
        $this->see('Forgot your password?');
        $this->see('We\'ve all been there. Reset by entering your email.');
        $this->submitForm('Request New Password', [
            'email' => 'forgetful@example.com',
        ]);

        // We'll assert that the event was created & take note of reset URL for the next step.
        Bus::assertDispatched(SendPasswordResetToCustomerIo::class, function (
            $job
        ) use (&$resetPasswordUrl) {
            $resetPasswordUrl = $job->getUrl();

            return true;
        });
        info('testForgotPasswordResetFlow ' . $resetPasswordUrl);

        // The user should visit the link that was sent via email & set a new password.
        $this->visit($resetPasswordUrl);
        $this->dontSee('window.snowplow');
        $this->postForm('Reset Password', [
            'password' => 'new-top-secret-passphrase',
            'password_confirmation' => 'new-top-secret-passphrase',
        ]);

        // The user should be logged-in to Northstar, and redirected to Phoenix's OAuth flow.
        $this->seeIsAuthenticated();
        $this->seeIsAuthenticatedAs($user, 'web');
        $this->assertRedirectedTo(
            config('services.phoenix.url') . '/next/login',
        );

        // And their account should be updated with their new password.
        $this->assertTrue(
            app(Registrar::class)->validateCredentials($user->fresh(), [
                'password' => 'new-top-secret-passphrase',
            ]),
        );
    }

    /**
     * Test that users can't request a password reset for another user and flood their email,
     * and mitigate brute-force guessing an existing email via enumeration.
     */
    public function testPasswordResetRateLimited()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->visit('password/reset');
            $this->submitForm('Request New Password', [
                'email' => 'nonexistant@example.com',
            ]);

            $this->see('We can\'t find a user with that e-mail address.');
        }

        $this->expectsEvents(\App\Events\Throttled::class);

        $this->visit('password/reset');
        $this->submitForm('Request New Password', [
            'email' => 'nonexistant@example.com',
        ]);

        $this->see('Too many attempts.');
    }

    /**
     * Test that the Reset Password form displays Activate Account per type query parameter.
     */
    public function testActivateAccountResetFlow()
    {
        Bus::fake();

        $user = factory(User::class)->create();
        $resetPasswordUrl = null;

        $this->asAdminUser()->post('v2/resets', [
            'id' => $user->id,
            'type' => 'rock-the-vote-activate-account',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure(['success']);

        // We'll assert that the event was created & take note of reset URL for the next step.
        Bus::assertDispatched(SendPasswordResetToCustomerIo::class, function (
            $job
        ) use (&$resetPasswordUrl) {
            $resetPasswordUrl = $job->getUrl();

            return true;
        });

        $this->refreshApplication();

        $this->visit($resetPasswordUrl);
        $this->see('Welcome to your DoSomething.org account!');
        $this->see(
            'Create a password to join a movement of young people dedicated to making their communities a better place for everyone.',
        );

        $this->postForm('Activate Account', [
            'password' => 'new-top-secret-passphrase',
            'password_confirmation' => 'new-top-secret-passphrase',
        ]);

        // The user should be logged-in to Northstar, and redirected to Phoenix's OAuth flow.
        $this->seeIsAuthenticated();
        $this->seeIsAuthenticatedAs($user, 'web');
        $this->assertRedirectedTo('profile/about');

        // And their account should be updated with their new password.
        $this->assertTrue(
            app(Registrar::class)->validateCredentials($user->fresh(), [
                'password' => 'new-top-secret-passphrase',
            ]),
        );
    }
}
