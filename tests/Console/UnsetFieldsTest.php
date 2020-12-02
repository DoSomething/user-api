<?php

use Illuminate\Support\Facades\Artisan;
use Northstar\Models\User;

class UnsetFieldsTest extends TestCase
{
    /** @test */
    public function it_should_remove_fields()
    {
        // Create some users
        factory(User::class, 5)->create();

        // Make sure all users have a `city`
        $usersWithCity = User::whereRaw([
            'city' => [
                '$exists' => true,
            ],
        ])->count();
        $this->assertEquals($usersWithCity, 5);

        // Make sure all users have a `sms_status`
        $usersWithSmsStatus = User::whereRaw([
            'sms_status' => [
                '$exists' => true,
            ],
        ])->count();
        $this->assertEquals($usersWithSmsStatus, 5);

        // Make sure all users have a `country`
        $usersWithCountry = User::whereRaw([
            'country' => [
                '$exists' => true,
            ],
        ])->count();
        $this->assertEquals($usersWithCountry, 5);

        // Run the command to unset `city` and `country`
        Artisan::call('northstar:unset', [
            'field' => ['city', 'country'],
            '--force' => true,
        ]);

        // Make sure NO users have a `city`
        $usersWithCity = User::whereRaw([
            'city' => [
                '$exists' => true,
            ],
        ])->count();
        $this->assertEquals($usersWithCity, 0);

        // Make sure all users have a `sms_status`
        $usersWithSmsStatus = User::whereRaw([
            'sms_status' => [
                '$exists' => true,
            ],
        ])->count();
        $this->assertEquals($usersWithSmsStatus, 5);

        // Make sure NO users have a `country`
        $usersWithCountry = User::whereRaw([
            'country' => [
                '$exists' => true,
            ],
        ])->count();
        $this->assertEquals($usersWithCountry, 0);
    }
}
