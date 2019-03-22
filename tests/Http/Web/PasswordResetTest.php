<?php

use Northstar\Jobs\SendPasswordResetToCustomerIo;
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
    public function testForgotPasswordResetFlow()
    {
        Bus::fake();

        $user = factory(User::class)->create(['email' => 'forgetful@example.com']);
        $resetPasswordUrl = '';

        // The user should be able to request a new password by entering their email.
        $this->visit('/password/reset');
        $this->see('Forgot your password?');
        $this->see('We\'ve all been there. Reset by entering your email.');
        $this->submitForm('Request New Password', [
            'email' => 'forgetful@example.com',
        ]);

        // We'll assert that the event was created & take note of reset URL for the next step.
        Bus::assertDispatched(SendPasswordResetToCustomerIo::class, function ($job) use (&$resetPasswordUrl) {
            $resetPasswordUrl = $job->getUrl();

            return true;
        });
        info('testForgotPasswordResetFlow '.$resetPasswordUrl);

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

    /**
     * Test that the Reset Password form displays Activate Account per type query parameter.
     */
    public function testActivateAccountResetFlow()
    {
        Bus::fake();

        $user = factory(User::class)->create();
        $passwordResetUrl = '';

        /*
        $this->asAdminUser()->post('v2/resets', [
            'id' => $user->id,
            'type' => 'rock-the-vote-activate-account',
        ]);
        $this->assertResponseStatus(200);
        $this->seeJsonStructure(['success']);
        // We'll assert that the event was created & take note of reset URL for the next step.
        Bus::assertDispatched(SendPasswordResetToCustomerIo::class, function ($job) use (&$resetPasswordUrl) {
            $passwordResetUrl = $job->getUrl();

            return true;
        });
        */

        $resetPasswordUrl = 'password/reset/f4da1d2ab8ba48ac992518933653beb7a59d57dc5a45275b083ec2b02a1528dc?email=kdeckow%40example.com&type=rock-the-vote-activate-account';

        $this->visit($resetPasswordUrl);
        $this->see('Welcome to your DoSomething.org account!');
        $this->see('Create a password to join a movement of young people dedicated to making their communities a better place for everyone.');
        /*
        $this->postForm('Activate Account', [
            'password' => 'top_secret',
            'password_confirmation' => 'top_secret',
        ]);

        // The user should be logged-in to Northstar, and redirected to Phoenix's OAuth flow.
        $this->seeIsAuthenticatedAs($user, 'web');
        $this->assertRedirectedTo('https://www-dev.dosomething.org/next/login');

        // And their account should be updated with their new password.
        $this->assertTrue(app(Registrar::class)->validateCredentials($user->fresh(), ['password' => 'top_secret']));
        */
    }
}
