<?php

namespace Tests\Console;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\BrowserKitTestCase;

class RemoveCustomerIoMobileTest extends BrowserKitTestCase
{
    /** @test */
    public function testExpectedApiCalls()
    {
        factory(User::class, 5)
            ->states('email-subscribed', 'sms-subscribed')
            ->create();

        $nullSmsStatusUsers = factory(User::class, 3)
            ->states('email-unsubscribed')
            ->create(['sms_status' => null]);
        $unsubscribedToSmsUsers = factory(User::class, 3)
            ->states('email-subscribed', 'sms-unsubscribed')
            ->create();
        $unsubscribedToAllUsers = factory(User::class, 7)
            ->states('email-unsubscribed', 'sms-unsubscribed')
            ->create();

        Artisan::call('northstar:cio-remove-mobile');

        // Called 8 times for creating a subscribed user, another 3 when console command executed.
        $this->customerIoMock->shouldHaveReceived('updateCustomer')->times(11);
        $this->customerIoMock->shouldHaveReceived('deleteCustomer')->times(10);

        foreach ($nullSmsStatusUsers as $user) {
            $this->assertNotNull($user->fresh()->promotions_muted_at);
        }

        foreach ($unsubscribedToSmsUsers as $user) {
            $this->assertNull($user->fresh()->promotions_muted_at);
        }

        foreach ($unsubscribedToAllUsers as $user) {
            $this->assertNotNull($user->fresh()->promotions_muted_at);
        }
    }
}
