<?php

use Northstar\Models\User;
use Illuminate\Support\Facades\Artisan;

class BackfillCustomerIoTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_backfill_users()
    {
        $users = factory(User::class, 5)->create();

        // Run the Customer.io backfill command.
        Artisan::call('northstar:cio');

        // Make sure we send to Customer.io the expected number of times (5 times when created, 5 times when we call the command)
        $this->customerIoMock->shouldHaveReceived('updateCustomer')->times(10);
    }
}
