<?php

namespace Tests\Console;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class StandardizeBirthdatesTest extends TestCase
{
    /** @test */
    public function it_should_fix_birthdates()
    {
        // We are creating some bad data on purpose, so don't try to send it to Customer.io
        Bus::fake();

        // Create some regular and borked birthday users
        factory(User::class, 5)->create();

        $this->createMongoDocument('users', [
            'birthdate' => '2018-03-15 00:00:00',
        ]);

        $this->createMongoDocument('users', ['birthdate' => 'I love dogs']);

        // Make sure we are registering 2 borked users
        $borkedUsersCount = User::whereRaw([
            'birthdate' => [
                '$exists' => true,
                '$not' => [
                    '$type' => 9,
                ],
            ],
        ])->count();

        $this->assertEquals($borkedUsersCount, 2);

        // Run the Birthdate Standardizer command.
        Artisan::call('northstar:bday');

        // Make sure we have 0 borked users
        $borkedUsersCount = User::whereRaw([
            'birthdate' => [
                '$exists' => true,
                '$not' => [
                    '$type' => 9,
                ],
            ],
        ])->count();

        $this->assertEquals($borkedUsersCount, 0);
    }
}
