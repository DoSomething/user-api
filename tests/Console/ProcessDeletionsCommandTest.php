<?php

use Carbon\Carbon;
use Northstar\Models\User;
use Northstar\Services\Rogue;
use Northstar\Services\Gambit;
use Northstar\Services\CustomerIo;

class ProcessDeletionsCommandTest extends TestCase
{
    /** @test */
    public function it_should_delete_users()
    {
        // Create the expected users we're going to destroy:
        $user1 = factory(User::class)->create(['deletion_requested_at' => new Carbon('15 days ago')]);
        $user2 = factory(User::class)->create(['deletion_requested_at' => new Carbon('20 days ago')]);
        $user3 = factory(User::class)->create(['deletion_requested_at' => new Carbon('3 days ago')]);
        $user4 = factory(User::class)->create();

        // Mock the external service APIs & assert that we make two "delete" requests:
        $this->mock(Rogue::class)->shouldReceive('deleteUser')->twice();
        $this->mock(Gambit::class)->shouldReceive('deleteUser')->twice();
        $this->mock(CustomerIo::class)->shouldReceive('deleteUser')->twice();

        // Run the 'northstar:delete' command on the 'example-identify-output.csv' file:
        $this->artisan('northstar:process-deletions');

        // The command should remove the users queued for > 14 days:
        $this->assertAnonymized($user1);
        $this->assertAnonymized($user2);

        // ...but not the one that was queued more recently, or not at all:
        $this->assertArrayNotHasKey('deleted_at', $user3->fresh()->getAttributes());
        $this->assertArrayNotHasKey('deleted_at', $user4->fresh()->getAttributes());
    }

    /**
     * Assert that the given model has been anonymized.
     *
     * @param User $before
     */
    protected function assertAnonymized($before)
    {
        $after = $before->fresh();
        $attributes = $after->getAttributes();

        // The birthdate should be set to January 1st of the same year:
        $this->assertEquals($before->birthdate->year, $after->birthdate->year);
        $this->assertEquals(1, $after->birthdate->month);
        $this->assertEquals(1, $after->birthdate->day);

        // We should not see any fields with PII:
        $this->assertArrayNotHasKey('email', $attributes);
        $this->assertArrayNotHasKey('first_name', $attributes);
        $this->assertArrayNotHasKey('last_name', $attributes);
        $this->assertArrayNotHasKey('addr_street1', $attributes);
        $this->assertArrayNotHasKey('addr_street2', $attributes);

        // ...but we should still have some demographic fields:
        $this->assertArrayHasKey('addr_zip', $attributes);

        // We should also have set a "deleted at" flag:
        $this->assertArrayHasKey('deleted_at', $attributes);
    }
}
