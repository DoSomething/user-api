<?php

use Carbon\Carbon;
use Northstar\Models\User;

class RemoveOldBirthdatesTest extends TestCase
{
    /** @test */
    public function it_should_fix_birthdates()
    {
        // Create some normal and *really* old users:
        $normalUsers = factory(User::class, 5)->create();
        $ancientOnes = factory(User::class, 3)->create(['birthdate' => new Carbon('0001-01-01 00:00:00')]);

        // Run the script!
        $this->artisan('northstar:olds');

        // Make sure we have 0 borked users
        foreach ($normalUsers as $user) {
            $attributes = $user->fresh()->getAttributes();
            $this->assertArrayHasKey('birthdate', $user->fresh()->getAttributes(), 'Valid birthdates should not be removed.');
        }

        foreach ($ancientOnes as $user) {
            $attributes = $user->fresh()->getAttributes();
            $this->assertArrayNotHasKey('birthdate', $attributes, 'Invalid birthdates should be removed.');
        }
    }
}
