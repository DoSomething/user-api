<?php

namespace Tests\Console;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class FixSmsStatusCommandTest extends TestCase
{
    /** @test */
    public function it_should_fix_sms_statuses()
    {
        $addressable = ['active', 'less', 'pending'];

        // Create two "broken" users (with IDs in 'example-ids.csv'):
        $brokenUser1 = factory(User::class)->create([
            '_id' => '5d3630a0fdce2742ff6c64d4',
            'mobile' => null,
            'sms_status' => $this->faker->randomElement($addressable),
        ]);

        $brokenUser2 = factory(User::class)->create([
            '_id' => '5d3630a0fdce2742ff6c64d5',
            'mobile' => null,
            'sms_status' => $this->faker->randomElement($addressable),
        ]);

        $goodUser = factory(User::class)->create([
            'mobile' => $this->faker->unique()->phoneNumber,
            'sms_status' => $this->faker->randomElement($addressable),
        ]);

        // Run the fixer command.
        Artisan::call('northstar:fix-sms-status', [
            'input' => 'tests/Console/example-ids.csv',
        ]);

        // We should have removed this field for anyone who doesn't have a mobile:
        $this->assertNull($brokenUser1->fresh()->sms_status);
        $this->assertNull($brokenUser2->fresh()->sms_status);

        // And users that _should_ have SMS status should be untouched:
        $this->assertNotNull($goodUser->fresh()->sms_status);
    }
}
