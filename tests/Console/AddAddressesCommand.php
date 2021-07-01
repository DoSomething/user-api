<?php

namespace Tests\Console;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AddAddressesCommand extends TestCase
{
    /** @test */
    public function it_should_set_addresses()
    {
        // Make two test users (matching the IDs in the 'example-addresses.csv' file).
        User::forceCreate([
            '_id' => '54f9e1c8469c64df6c8b4568',
            'first_name' => 'Test',
        ]);
        User::forceCreate([
            '_id' => '54fa272c469c64d7068b456c',
            'first_name' => 'Dominique',
        ]);

        // Run the addresses command.
        Artisan::call('northstar:addr', [
            'path' => 'tests/Console/example-addresses.csv',
        ]);

        // And see that we stored the provided addresses!
        $this->assertMongoDatabaseHas('users', [
            '_id' => '54f9e1c8469c64df6c8b4568',
            'addr_street1' => '101 Main St',
            'addr_street2' => 'Apt 33',
            'addr_city' => 'Example',
            'addr_state' => 'CT',
            'addr_zip' => '55555',
            'country' => 'US',
        ]);

        $this->assertMongoDatabaseHas('users', [
            '_id' => '54fa272c469c64d7068b456c',
            'addr_street1' => null,
            'addr_street2' => null,
            'addr_city' => 'Portland',
            'addr_state' => 'ME',
            'addr_zip' => '04101',
            'country' => 'US',
        ]);
    }
}
