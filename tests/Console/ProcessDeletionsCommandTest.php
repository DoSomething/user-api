<?php

use App\Models\User;
use App\Services\Gambit;
use App\Services\Rogue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

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

        // Mock the external service APIs & assert that we make two "delete" requests:
        $this->mock(Rogue::class)
            ->shouldReceive('deleteUser')
            ->twice();
        $this->mock(Gambit::class)
            ->shouldReceive('deleteUser')
            ->twice();
        $this->customerIoMock->shouldReceive('suppressUser')->twice();

        // Run the 'northstar:delete' command on the 'example-identify-output.csv' file:
        Artisan::call('northstar:process-deletions');

        // The command should remove the users queued for > 14 days:
        $this->assertAnonymized($user1);
        $this->assertAnonymized($user2);

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
