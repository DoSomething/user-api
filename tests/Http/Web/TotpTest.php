<?php

namespace Tests\Http\Web;

use App\Models\User;
use Tests\BrowserKitTestCase;

class TotpTest extends BrowserKitTestCase
{
    /**
     * Default headers for this test case.
     *
     * @var array
     */
    protected $headers = [
        'Accept' => 'text/html',
    ];

    public function loginWithTotp()
    {
        $totp = \OTPHP\TOTP::create();
        $totp->setLabel('phpunit');

        $user = factory(User::class)->create([
            'email' => 'login-test@dosomething.org',
            'password' => 'secret',
            'totp' => $totp->getProvisioningUri(),
        ]);

        $this->visit('login')
            ->type('login-test@dosomething.org', 'username')
            ->type('secret', 'password')
            ->press('Log In');

        return [$user, $totp];
    }

    /**
     * Test that users with two-factor enabled are asked
     * to provide a valid TOTP code.
     */
    public function testLoginWithValidTotpCode()
    {
        [$user, $totp] = $this->loginWithTotp();

        $this->see('This account is protected by two-factor authentication.');

        $this->type($totp->now(), 'code')->press('Verify');

        $this->seeIsAuthenticatedAs($user, 'web');
    }

    /**
     * Test that a bad two-factor code is rejected.
     */
    public function testLoginWithInvalidTotpCode()
    {
        [$user, $totp] = $this->loginWithTotp();

        $this->see('This account is protected by two-factor authentication.');

        $this->type('000000', 'code')->press('Verify');

        $this->see('That wasn\'t a valid two-factor code. Try again!');
        $this->dontSeeIsAuthenticated('web');
    }
}
