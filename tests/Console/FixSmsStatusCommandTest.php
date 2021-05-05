<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class FixSmsStatusCommandTest extends TestCase
{
    /** @test */
    public function it_should_fix_sms_statuses()
    {
        // We should have
        $brokenUsers = factory(User::class, 5)->create([
            'mobile' => null,
            'sms_status' => $this->faker->randomElement([
                'active',
                'less',
                'pending',
            ]),
        ]);

        $goodUser = factory(User::class)->create([
            'mobile' => $this->faker->unique()->phoneNumber,
            'sms_status' => $this->faker->randomElement([
                'active',
                'less',
                'pending',
            ]),
        ]);

        // Run the fixer command.
        Artisan::call('northstar:fix-sms-status');

        // We should have removed this field for anyone who doesn't have a mobile:
        foreach ($brokenUsers as $user) {
            $this->assertNull($user->fresh()->sms_status);
        }

        // And users that _should_ have SMS status should be untouched:
        $this->assertNotEquals('stop', $goodUser->fresh()->sms_status);
    }
}
