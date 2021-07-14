<?php

namespace Tests\Console;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class IdentifyUsersCommandTest extends TestCase
{
    /** @test */
    public function it_should_identify_users()
    {
        $input = 'tests/Console/example-identify-input.csv';
        $output = tempnam(sys_get_temp_dir(), 'IdentifyUsersCommandTest');

        // Create the expected users we're going to identify:
        User::forceCreate([
            '_id' => '5d3630a0fdce2742ff6c64d4',
            'email' => 'sporer.winston@example.org',
        ]);
        User::forceCreate([
            '_id' => '5d3630a0fdce2742ff6c64d5',
            'email' => 'haylee.buckridge@example.org',
        ]);

        // Run the 'northstar:id' command on the 'example-identify-input.csv' file:
        Artisan::call('northstar:id', [
            'input' => $input,
            'output' => $output,
            '--csv_column' => 'Requester email address',
        ]);

        // The command should create a CSV matching our expected output:
        $this->assertFileEquals(
            'tests/Console/example-identify-output.csv',
            $output,
        );
    }
}
