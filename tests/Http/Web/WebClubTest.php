<?php

use App\Models\Club;
use App\Models\User;

class ClubTest extends TestCase
{
    /**
     * Test that admin can create a new club.
     *
     * POST /clubs
     * @return void
     */
    public function testAdminCanCreateClub()
    {
        $admin = factory(User::class, 'admin')->create();

        $name = $this->faker->sentence;

        $leaderId = factory(User::class)->create()->id;

        $response = $this->actingAs($admin, 'web')->post('/clubs', [
            'name' => $name,
            'leader_id' => $leaderId,
        ]);

        $response->assertRedirect();

        $this->assertMysqlDatabaseHas('clubs', [
            'name' => $name,
            'leader_id' => $leaderId,
        ]);
    }

    /**
     * Test that staff can create a new club.
     *
     * POST /clubs
     * @return void
     */
    public function testStaffCanCreateClub()
    {
        $staff = factory(User::class, 'staff')->create();

        $name = $this->faker->sentence;

        $leaderId = factory(User::class)->create()->id;

        $response = $this->actingAs($staff, 'web')->post('/clubs', [
            'name' => $name,
            'leader_id' => $leaderId,
        ]);

        $response->assertRedirect();

        $this->assertMysqlDatabaseHas('clubs', [
            'name' => $name,
            'leader_id' => $leaderId,
        ]);
    }

    /**
     * Test validation for creating a club.
     *
     * POST /clubs
     * @return void
     */
    public function testCreatingAClubWithValidationErrors()
    {
        $admin = factory(User::class, 'admin')->create();

        $response = $this->actingAs($admin, 'web')->post('/clubs', [
            'city' => 789, // This should be a string.
            'name' => 123, // This should be a string.
            'leader_id' => 'Maddy is the leader!', // This should be a MongoDB ObjectID.
            'location' => 'wakanda', // This should be an iso3166 string.
            'school_id' => 101112, // This should be a string.
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'city',
            'name',
            'leader_id',
            'location',
            'school_id',
        ]);
    }

    /**
     * Test validation for creating a club with a duplicate leader_id.
     *
     * POST /clubs
     * @return void
     */
    public function testCreatingAClubWithDuplicateLeaderId()
    {
        $this->markTestSkipped();

        $admin = factory(User::class, 'admin')->create();

        $club = factory(Club::class)->create();

        $response = $this->actingAs($admin, 'web')->post('/clubs', [
            'name' => $this->faker->company,
            'leader_id' => $club->leaderId,
        ]);

        $response->assertJsonValidationErrors(['leader_id']);
    }

    /**
     * Test for updating a club successfully.
     *
     * PATCH /clubs/:id
     * @return void
     */
    public function testUpdatingAClub()
    {
        $this->faker->addProvider(new FakerNorthstarId($this->faker));
        $this->faker->addProvider(new FakerSchoolId($this->faker));

        $admin = factory(User::class, 'admin')->create();

        $club = factory(Club::class)->create();

        $name = $this->faker->company;

        $leaderId = $this->faker->unique()->northstar_id;
        // $leaderId = factory(User::class)->create()->id; // @TODO: which is better?

        $location = 'US-' . $this->faker->stateAbbr;

        $city = $this->faker->city;

        $schooolId = $this->faker->school->school_id;

        $response = $this->actingAs($admin, 'web')->patch(
            '/clubs' . '/' . $club->id,
            [
                'name' => $name,
                'leader_id' => $leaderId,
                'location' => $location,
                'city' => $city,
                'school_id' => $schooolId,
            ],
        );

        $response->assertRedirect();

        // Make sure that the club's updated attributes are persisted in the database.
        $this->assertEquals($club->fresh()->name, $name);
        $this->assertEquals($club->fresh()->leader_id, $leaderId);
        $this->assertEquals($club->fresh()->location, $location);
        $this->assertEquals($club->fresh()->city, $city);
        $this->assertEquals($club->fresh()->school_id, $schooolId);
    }

    /**
     * Test for updating a club without changing the leader_id successfully.
     *
     * PATCH /clubs/:id
     * @return void
     */
    public function testUpdatingAClubWithoutChangingTheLeaderId()
    {
        $this->markTestSkipped();

        $admin = factory(User::class, 'admin')->create();

        $club = factory(Club::class)->create();

        $name = $this->faker->company;

        $response = $this->actingAs($admin, 'web')->patchJson(
            'clubs/' . $club->id,
            [
                'name' => $name,
                'leader_id' => $club->leader_id,
            ],
        );

        $response->assertStatus(302);

        // Make sure that the club's updated attributes are persisted in the database.
        $this->assertEquals($club->fresh()->name, $name);
        $this->assertEquals($club->fresh()->leader_id, $club->leader_id);
    }

    /**
     * test validation for updating a club.
     *
     * PATCH /clubs/:id
     * @return void
     */
    public function testUpdatingAClubWithValidationErrors()
    {
        $this->markTestSkipped();

        $admin = factory(User::class, 'admin')->create();

        $club = factory(Club::class)->create();

        $this->actingAs($admin, 'web')
            ->patchJson('clubs/' . $club->id, [
                'name' => 123, // This should be a string.
                'leader_id' => 'Maddy is the leader!', // This should be a MongoDB ObjectID.
                'location' => 'wakanda', // This should be an iso3166 string.
                'city' => 789, // This should be a string.
                'school_id' => 101112, // This should be a string.
            ])
            ->assertJsonValidationErrors([
                'name',
                'leader_id',
                'location',
                'city',
                'school_id',
            ]);
    }
}
