<?php

namespace Tests\Http\Web;

use App\Models\Club;
use App\Models\User;
use Tests\TestCase;

class WebClubTest extends TestCase
{
    /**
     * Test that admin can create a new club.
     *
     * POST /clubs
     * @return void
     */
    public function testAdminCanCreateClub()
    {
        $admin = factory(User::class)->states('admin')->create();

        $leaderId = factory(User::class)->create()->id;

        $name = $this->faker->sentence;

        $response = $this->actingAs($admin, 'web')->post('/admin/clubs', [
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
        $staff = factory(User::class)->states('staff')->create();

        $leaderId = factory(User::class)->create()->id;

        $name = $this->faker->sentence;

        $response = $this->actingAs($staff, 'web')->post('/admin/clubs', [
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
        $admin = factory(User::class)->states('admin')->create();

        $response = $this->actingAs($admin, 'web')->post('/admin/clubs', [
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
        $admin = factory(User::class)->states('admin')->create();

        $club = factory(Club::class)->create();

        $response = $this->actingAs($admin, 'web')->post('/admin/clubs', [
            'name' => $this->faker->company,
            'leader_id' => $club->leaderId,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['leader_id']);
    }

    /**
     * Test for updating a club successfully.
     *
     * PATCH /clubs/:id
     * @return void
     */
    public function testUpdatingAClub()
    {
        $admin = factory(User::class)->states('admin')->create();

        $leaderId = factory(User::class)->create()->id;

        $club = factory(Club::class)->create();

        $name = $this->faker->company;

        $location = 'US-' . $this->faker->stateAbbr;

        $city = $this->faker->city;

        $schooolId = $this->faker->school->school_id;

        $response = $this->actingAs($admin, 'web')->patch(
            "/admin/clubs/$club->id",
            [
                'name' => $name,
                'leader_id' => $leaderId,
                'location' => $location,
                'city' => $city,
                'school_id' => $schooolId,
            ],
        );

        $response->assertRedirect();

        $this->assertMysqlDatabaseHas('clubs', [
            'name' => $name,
            'leader_id' => $leaderId,
            'location' => $location,
            'city' => $city,
            'school_id' => $schooolId,
        ]);
    }

    /**
     * Test for updating a club without changing the leader_id successfully.
     *
     * PATCH /clubs/:id
     * @return void
     */
    public function testUpdatingAClubWithoutChangingTheLeaderId()
    {
        $admin = factory(User::class)->states('admin')->create();

        $club = factory(Club::class)->create();

        $name = $this->faker->company;

        $response = $this->actingAs($admin, 'web')->patch(
            "/admin/clubs/$club->id",
            [
                'name' => $name,
                'leader_id' => $club->leader_id,
            ],
        );

        $response->assertRedirect();

        $this->assertMysqlDatabaseHas('clubs', [
            'name' => $name,
            'leader_id' => $club->leader_id,
        ]);
    }

    /**
     * test validation for updating a club.
     *
     * PATCH /clubs/:id
     * @return void
     */
    public function testUpdatingAClubWithValidationErrors()
    {
        $admin = factory(User::class)->states('admin')->create();

        $club = factory(Club::class)->create();

        $response = $this->actingAs($admin, 'web')->patch(
            "/admin/clubs/$club->id",
            [
                'name' => 123, // This should be a string.
                'leader_id' => 'Maddy is the leader!', // This should be a MongoDB ObjectID.
                'location' => 'wakanda', // This should be an iso3166 string.
                'city' => 789, // This should be a string.
                'school_id' => 101112, // This should be a string.
            ],
        );

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'name',
            'leader_id',
            'location',
            'city',
            'school_id',
        ]);
    }
}
