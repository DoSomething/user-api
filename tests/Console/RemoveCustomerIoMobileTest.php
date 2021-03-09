<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class RemoveCustomerIoMobileTest extends BrowserKitTestCase
{
    /** @test */
    public function testExpectedApiCalls()
    {
        factory(User::class, 5)->states('email-subscribed', 'sms-subscribed')->create();
        factory(User::class, 3)->states('email-subscribed', 'sms-unsubscribed')->create();
        factory(User::class, 7)->states('email-unsubscribed', 'sms-unsubscribed')->create();

        Artisan::call('northstar:cio-remove-mobile');

        // Called 8 times for creating a subscribed user, another 3 when console command executed.
        $this->customerIoMock->shouldHaveReceived('updateCustomer')->times(11);
        $this->customerIoMock->shouldHaveReceived('deleteCustomer')->times(7);
    }
}
