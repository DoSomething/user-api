<?php

use App\Auth\Registrar;
use App\Jobs\SendPasswordResetToCustomerIo;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

class PasswordResetTest extends TestCase
{
    private function getUserTokenFromResetUrl($url)
    {
        return last(explode('/', parse_url($url)['path']));
    }

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

        // User can initiate a password reset.
        $stepOneResponse = $this->get('/password/reset');

        $stepOneResponse->assertSeeText('Forgot your password?');
        $stepOneResponse->assertSeeText(
            e('We\'ve all been there. Reset by entering your email.'),
        );

        // The user should be able to request a new password by entering their email.
        $stepTwoResponse = $this->post('/password/email', [
            'email' => 'forgetful@example.com',
        ]);

        $stepTwoResponse->assertStatus(302);
        $stepTwoResponse->assertRedirect('/password/reset');

        // Assert that the event was created & take note of reset URL for the next step.
        Bus::assertDispatched(SendPasswordResetToCustomerIo::class, function (
            $job
        ) use (&$resetPasswordUrl) {
            $resetPasswordUrl = $job->getUrl();

            return true;
        });

        // @TODO: Not sure why we are logging this, and if so should we do it for the test below with a $resetPasswordUrl?
        info('testForgotPasswordResetFlow ' . $resetPasswordUrl);

        // The user should visit the link that was sent via email & set a new password.
        $stepThreeResponse = $this->get($resetPasswordUrl);

        $stepThreeResponse->assertDontSee('window.snowplow');
        $stepThreeResponse->assertSeeText('Forgot your password?');

        $stepFourResponse = $this->post('/password/reset/forgot-password', [
            'email' => $user->email,
            'token' => $this->getUserTokenFromResetUrl($resetPasswordUrl),
            'password' => 'new-top-secret-passphrase',
            'password_confirmation' => 'new-top-secret-passphrase',
        ]);

        $stepFourResponse->assertStatus(302);
        $stepFourResponse->assertRedirect(
            config('services.phoenix.url') . '/next/login',
        );

        $this->followRedirects($stepFourResponse);

        // The user should be logged-in to Northstar, and redirected to Phoenix's OAuth flow.
        $this->isAuthenticated();
        $this->assertAuthenticatedAs($user, 'web');

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
            $this->get('password/reset')->assertStatus(200);

            $this->followingRedirects()
                ->post('/password/email', [
                    'email' => 'nonexistant@example.com',
                ])
                ->assertSeeText(
                    e('We can\'t find a user with that e-mail address.'),
                );
        }

        $this->expectsEvents(\App\Events\Throttled::class);

        $this->get('password/reset')->assertStatus(200);

        $this->followingRedirects()
            ->post('/password/email', [
                'email' => 'nonexistant@example.com',
            ])
            ->assertSeeText('Too many attempts.');
    }

    /**
     * Test that the Reset Password form displays Activate Account per type query parameter.
     */
    public function testActivateAccountResetFlow()
    {
        Bus::fake();

        $user = factory(User::class)->create();

        $resetPasswordUrl = null;

        // Initiate password reset for user.
        $stepOneResponse = $this->asAdminUser()->post('v2/resets', [
            'id' => $user->id,
            'type' => 'rock-the-vote-activate-account',
        ]);

        $stepOneResponse->assertStatus(200);
        $stepOneResponse->assertJsonStructure(['success']);

        // Assert that the event was created & take note of reset URL for the next step.
        Bus::assertDispatched(SendPasswordResetToCustomerIo::class, function (
            $job
        ) use (&$resetPasswordUrl) {
            $resetPasswordUrl = $job->getUrl();

            return true;
        });

        $this->refreshApplication();

        // User goes to password reset URL.
        $stepTwoResponse = $this->get($resetPasswordUrl);

        $stepTwoResponse->assertSeeText(
            'Welcome to your DoSomething.org account!',
        );

        $stepTwoResponse->assertSeeText(
            'Create a password to join a movement of young people dedicated to making their communities a better place for everyone.',
        );

        // User submits post request with new password.
        $stepThreeResponse = $this->post(
            '/password/reset/rock-the-vote-activate-account',
            [
                'email' => $user->email,
                'token' => $this->getUserTokenFromResetUrl($resetPasswordUrl),
                'password' => 'new-top-secret-passphrase',
                'password_confirmation' => 'new-top-secret-passphrase',
            ],
        );

        $stepThreeResponse->assertStatus(302);
        $stepThreeResponse->assertRedirect('profile/about');

        // Continue redirect to complete process and land on about or edit profile page.
        $this->followRedirects($stepThreeResponse);

        // The user should be logged-in to Northstar, and redirected to Phoenix's OAuth flow.
        $this->isAuthenticated();
        $this->assertAuthenticatedAs($user, 'web');

        // And their account should be updated with their new password.
        $this->assertTrue(
            app(Registrar::class)->validateCredentials($user->fresh(), [
                'password' => 'new-top-secret-passphrase',
            ]),
        );
    }
}
