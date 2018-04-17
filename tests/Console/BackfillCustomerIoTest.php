<?php

use Northstar\Models\User;
use DoSomething\Gateway\Blink;
use Northstar\Jobs\SendUserToCustomerIo;

class BackfillCustomerIoTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_backfill_users()
    {
        $users = factory(User::class, 5)->create();

        // Run the Customer.io backfill command.
        $this->artisan('northstar:cio');

        // I think that this is the best way to test this (5 times when created, 5 times when we call the command)
        $this->blinkMock->shouldHaveReceived('userCreate')->times(10);
    }
}
