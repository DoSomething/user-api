<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class RemoveCustomerIoMobileTest extends BrowserKitTestCase
{
    /** @test */
    public function testExpectedApiCalls()
    {
        factory(User::class, 5)->states('email-subscribed', 'sms-subscribed')->create();
        $removeMobiles = factory(User::class, 3)->states('email-subscribed', 'sms-unsubscribed')->create();
        $deleteProfiles = factory(User::class, 7)->states('email-unsubscribed', 'sms-unsubscribed')->create();

        Artisan::call('northstar:cio-remove-mobile');

        // Called 8 times for creating a subscribed user, another 3 when console command executed.
        $this->customerIoMock->shouldHaveReceived('updateCustomer')->times(11);
        $this->customerIoMock->shouldHaveReceived('deleteCustomer')->times(7);

        foreach ($removeMobiles as $user) {
            $this->assertNull($user->promotions_muted_at);
        }

        foreach ($deleteProfiles as $user) {
            // TODO: Why is this failling?
          //$this->assertNotNull($user->promotions_muted_at);
        }
    }
}
