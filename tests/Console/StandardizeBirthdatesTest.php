<?php

use Northstar\Models\User;

class StandardizeBirthdatesTest extends TestCase
{
    /** @test */
    public function it_should_fix_birthdates()
    {
        // Create some regular and borked birthday users
        factory(User::class, 5)->create();
        $this->createMongoDocument('users', ['birthdate' => '2018-03-15 00:00:00.000']);
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
        $this->assertEquals($borkedUsers, 2);

        // Run the Birthdate Standardizer command.
        $this->artisan('northstar:bday');

        // Make sure we have 0 borked users
        $borkedUsersCount = User::whereRaw([
            'birthdate' => [
                '$exists' => true,
                '$not' => [
                    '$type' => 9,
                ],
            ],
        ])->count();
        $this->assertEquals($borkedUsers, 0);
    }
}
