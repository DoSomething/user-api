<?php

use Northstar\Models\User;

class DeleteUsersCommandTest extends TestCase
{
    /** @test */
    public function it_should_delete_users()
    {
        $now = $this->mockTime();
        $input = 'tests/Console/example-identify-output.csv';

        // Create the expected users we're going to destroy:
        $user1 = factory(User::class)->create(['_id' => '5d3630a0fdce2742ff6c64d4'])->first();
        $user2 = factory(User::class)->create(['_id' => '5d3630a0fdce2742ff6c64d5'])->first();

        // Run the 'northstar:delete' command on the 'example-identify-output.csv' file:
        $this->artisan('northstar:delete', ['input' => $input]);

        // The command should remove
        $this->assertEquals($user1->fresh()->deletion_requested_at, $now);
        $this->assertEquals($user2->fresh()->deletion_requested_at, $now);
    }
}
