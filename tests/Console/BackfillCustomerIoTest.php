<?php

use Northstar\Models\User;
use Northstar\Services\CustomerIo;

class BackfillCustomerIoTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_backfill_users()
    {
        factory(User::class, 5)->create();

        // Reset our Blink mock & set expectation that it'll be called 5 times.
        $this->mock(CustomerIo::class)->shouldReceive('updateProfile')->times(5);

        // Run the Customer.io backfill command.
        $this->artisan('northstar:cio');
    }
}
