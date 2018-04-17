<?php

use Northstar\Models\User;
use DoSomething\Gateway\Blink;
use Northstar\Jobs\SendUserToCustomerIo;

class BackfillCustomerIoTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_backfill_users()
    {
        // Bus::fake();

        $users = factory(User::class, 5)->create();

        // with this there is really no way to tell that each got sent over twice
        // Mock SendUserToCustomerIo job & make sure it gets called for each new user
        // foreach ($users as $user) {
        //     Bus::assertDispatched(SendUserToCustomerIo::class, function ($job) use ($user) {
        //             return $user->id === $job->getUserId();
        //     });
        // }

        // I think that this is the best way to test this (5 times when created, 5 times when we call the command)
        $this->blinkMock->shouldHaveReceived('userCreate')->times(10);

        // Run the Customer.io backfill command.
        $this->artisan('northstar:cio');
    }
}
