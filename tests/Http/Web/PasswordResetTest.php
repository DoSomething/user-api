<?php

use Northstar\Jobs\SendCallToActionEmailToCustomerIo;
use Illuminate\Support\Facades\Bus;
use Northstar\Auth\Registrar;
use Northstar\Models\User;

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
    public function testPasswordResetFlow()
    {
        Bus::fake();

        $user = factory(User::class)->create(['email' => 'forgetful@example.com']);
        $resetPasswordUrl = '';

        // The user should be able to request a new password by entering their email.
        $this->visit('/password/reset');
        $this->see('Forgot your password?');
        $this->submitForm('Request New Password', [
            'email' => 'forgetful@example.com',
        ]);

        // We'll assert that the event was created & take note of the token for the next step.
        // TODO: This should be assertDispatched!
        Bus::assertNotDispatched(SendCallToActionEmailToCustomerIo::class, function ($job) use (&$resetPasswordUrl) {
            $resetPasswordUrl = $job->params['actionUrl'];
        });

        // The user should visit the link that was sent via email & set a new password.
        $this->visit($resetPasswordUrl);
        $this->postForm('Reset Password', [
            'password' => 'top_secret',
            'password_confirmation' => 'top_secret',
        ]);

        // The user should be logged-in to Northstar, and redirected to Phoenix's OAuth flow.
        $this->seeIsAuthenticatedAs($user, 'web');
        $this->assertRedirectedTo('https://www-dev.dosomething.org/next/login');

        // And their account should be updated with their new password.
        $this->assertTrue(app(Registrar::class)->validateCredentials($user->fresh(), ['password' => 'top_secret']));
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

        $this->expectsEvents(\Northstar\Events\Throttled::class);

        $this->visit('password/reset');
        $this->submitForm('Request New Password', [
            'email' => 'nonexistant@example.com',
        ]);

        $this->see('Too many attempts.');
    }
}
