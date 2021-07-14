<?php

namespace Tests\Console;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DeleteUsersCommandTest extends TestCase
{
    /** @test */
    public function it_should_delete_users()
    {
        $this->mockTime();

        $input = 'tests/Console/example-identify-output.csv';

        // Create the expected users we're going to destroy:
        $user1 = factory(User::class)
            ->create(['_id' => '5d3630a0fdce2742ff6c64d4'])
            ->first();

        $user2 = factory(User::class)
            ->create(['_id' => '5d3630a0fdce2742ff6c64d5'])
            ->first();

        // Run the 'northstar:delete' command on the 'example-identify-output.csv' file:
        Artisan::call('northstar:delete', ['input' => $input]);

        // Did we delete these two users from external services?
        $this->gambitMock->shouldHaveReceived('deleteUser')->twice();
        $this->customerIoMock->shouldHaveReceived('suppressCustomer')->twice();

        // The command should remove
        $this->assertUserAnonymized($user1);
        $this->assertUserAnonymized($user2);
    }
}
