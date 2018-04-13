<?php

use Northstar\Models\User;
use DoSomething\Gateway\Blink;

class BackfillCustomerIoTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_backfill_users()
    {
        factory(User::class, 5)->create();

        // Mock Blink client & set expectation that it'll be called 10 times.
        $this->mock(Blink::class)->shouldReceive('userCreate')->times(10);

        // Run the Customer.io backfill command.
        $this->artisan('northstar:cio');
    }
}
