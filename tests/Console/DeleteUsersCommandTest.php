<?php

use App\Models\User;
use App\Services\Gambit;
use App\Services\Rogue;
use Illuminate\Support\Facades\Artisan;

class DeleteUsersCommandTest extends TestCase
{
    /** @test */
    public function it_should_delete_users()
    {
        $now = $this->mockTime();
        $input = 'tests/Console/example-identify-output.csv';

        // Create the expected users we're going to destroy:
        $user1 = factory(User::class)
            ->create(['_id' => '5d3630a0fdce2742ff6c64d4'])
            ->first();
        $user2 = factory(User::class)
            ->create(['_id' => '5d3630a0fdce2742ff6c64d5'])
            ->first();

        // Mock the external service APIs & assert that we make two "delete" requests:
        $this->mock(Rogue::class)
            ->shouldReceive('deleteUser')
            ->twice();
        $this->mock(Gambit::class)
            ->shouldReceive('deleteUser')
            ->twice();
        $this->customerIoMock->shouldReceive('deleteUser')->twice();

        // Run the 'northstar:delete' command on the 'example-identify-output.csv' file:
        Artisan::call('northstar:delete', ['input' => $input]);

        // The command should remove
        $this->assertUserAnonymized($user1);
        $this->assertUserAnonymized($user2);
    }
}
