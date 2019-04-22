<?php

use Northstar\Models\User;

class FixDrupalFieldsTest extends TestCase
{
    /** @test */
    public function it_should_format_fields()
    {
        $invalidFormatQuery = [
            '$exists' => true,
            '$not' => ['$type' => 'string'],
        ];

        // Create some users with regular and borked fields.
        factory(User::class, 5)->create();
        $this->createMongoDocument('users', ['email' => 'dave@example.com', 'addr_street1' => '19 W 21st St']);
        $this->createMongoDocument('users', ['email' => 'bob@example.com', 'addr_street1' => ['value' => '1 Main Street']]);
        $this->createMongoDocument('users', ['email' => 'hackz@example.com', 'addr_street1' => ['lol' => 'nonsense']]);

        // Make sure we have 2 borked users.
        $borkedUsersCount = User::whereRaw(['addr_street1' => $invalidFormatQuery])->count();
        $this->assertEquals(2, $borkedUsersCount);

        // Run the de-drupalification command.
        $this->artisan('northstar:dedrupal', ['field' => 'addr_street1']);

        // Now, we should have 0 borked users!
        $userWithValidField = User::where('email', 'dave@example.com')->first();
        $userWithParsableField = User::where('email', 'bob@example.com')->first();
        $userWithNonsenseField = User::where('email', 'hackz@example.com')->first();
        $newBorkedUsersCount = User::whereRaw(['addr_street1' => $invalidFormatQuery])->count();

        $this->assertEquals(0, $newBorkedUsersCount);
        $this->assertEquals('19 W 21st St', $userWithValidField->addr_street1);
        $this->assertEquals('1 Main Street', $userWithParsableField->addr_street1);
        $this->assertEquals(null, $userWithNonsenseField->addr_street1);
    }
}
