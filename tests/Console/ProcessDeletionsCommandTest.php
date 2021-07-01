<?php

namespace Tests\Console;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProcessDeletionsCommandTest extends TestCase
{
    /** @test */
    public function it_should_delete_users()
    {
        // Create the expected users we're going to destroy:
        $user1 = factory(User::class)->create([
            'deletion_requested_at' => new Carbon('15 days ago'),
        ]);
        $user2 = factory(User::class)->create([
            'deletion_requested_at' => new Carbon('20 days ago'),
        ]);
        $user3 = factory(User::class)->create([
            'deletion_requested_at' => new Carbon('3 days ago'),
        ]);
        $user4 = factory(User::class)->create();

        // Run the 'northstar:delete' command on the 'example-identify-output.csv' file:
        Artisan::call('northstar:process-deletions');

        // Did we delete these two users from external services?
        $this->gambitMock->shouldHaveReceived('deleteUser')->twice();
        $this->customerIoMock->shouldHaveReceived('suppressCustomer')->twice();

        // The command should remove the users queued for > 14 days:
        $this->assertUserAnonymized($user1);
        $this->assertUserAnonymized($user2);

        // ...but not the one that was queued more recently, or not at all:
        $this->assertArrayNotHasKey(
            'deleted_at',
            $user3->fresh()->getAttributes(),
        );
        $this->assertArrayNotHasKey(
            'deleted_at',
            $user4->fresh()->getAttributes(),
        );
    }
}
