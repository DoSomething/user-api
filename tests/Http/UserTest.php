<?php

use Carbon\Carbon;
use Northstar\Models\User;

class UserTest extends BrowserKitTestCase
{
    /**
     * Test retrieving multiple users.
     * GET /v2/users
     *
     * @return void
     */
    public function testV2IndexNotVisibleToUserRole()
    {
        // Make a normal user to test acting as.
        $user = factory(User::class)->create();

        // Make some test users to see in the index.
        factory(User::class, 5)->create();

        $this->asUser($user, ['role:admin'])->get('v2/users');
        $this->assertResponseStatus(401);
    }

    /**
     * Test retrieving multiple users.
     * GET /v2/users
     *
     * @return void
     */
    public function testV2IndexVisibleToStaffRole()
    {
        // Make a staff user & some test users.
        $staff = factory(User::class, 'staff')->create();
        factory(User::class, 5)->create();

        $this->asUser($staff, ['role:staff'])->get('v2/users');
        $this->assertResponseStatus(200);
    }

    /**
     * Test retrieving multiple users.
     * GET /v2/users
     *
     * @return void
     */
    public function testV2IndexVisibleToAdminRole()
    {
        // Make a admin & some test users.
        $admin = factory(User::class, 'admin')->create();
        factory(User::class, 5)->create();

        $this->asUser($admin, ['role:admin'])->get('v2/users');
        $this->assertResponseStatus(200);
    }

    /**
     * Test that we can filter records by date range.
     * GET /v2/users/
     *
     * @return void
     */
    public function testV2FilterByDateField()
    {
        factory(User::class, 4)->create(['updated_at' => $this->faker->dateTimeBetween('1/1/2000', '12/31/2009')]);
        factory(User::class, 5)->create(['updated_at' => $this->faker->dateTimeBetween('1/1/2010', '1/1/2015')]);
        factory(User::class, 6)->create(['updated_at' => $this->faker->dateTimeBetween('1/2/2015', '1/1/2017')]);

        $this->withAccessToken(['admin'])->json('GET', 'v2/users?before[updated_at]=1/1/2010');
        $this->assertCount(4, $this->decodeResponseJson()['data'], 'can filter `updated_at` before timestamp');

        $this->withAccessToken(['admin'])->json('GET', 'v2/users?after[updated_at]=1/1/2015');
        $this->assertCount(6, $this->decodeResponseJson()['data'], 'can filter `updated_at` after timestamp');

        $this->withAccessToken(['admin'])->json('GET', 'v2/users?before[updated_at]=1/2/2015&after[updated_at]=12/31/2009');
        $this->assertCount(5, $this->decodeResponseJson()['data'], 'can filter `updated_at` between two timestamps');
    }

    /**
     * Test that retrieving a user as a non-admin returns limited profile.
     * GET /v2/users/:term/:id
     *
     * @return void
     */
    public function testV2GetPublicDataFromUser()
    {
        $user = factory(User::class)->create();
        $viewer = factory(User::class)->create();

        // Test that we can view public profile as another user.
        $this->asUser($viewer, ['user', 'user:admin'])->get('v2/users/_id/'.$user->id);
        $this->assertResponseStatus(200);

        // And test that private profile fields are hidden for the other user.
        $data = $this->decodeResponseJson()['data'];
        $this->assertArrayHasKey('first_name', $data);
        $this->assertArrayNotHasKey('last_name', $data);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('mobile', $data);
        $this->assertArrayNotHasKey('facebook_id', $data);
    }

    /**
     * Test that retrieving a user as an admin returns full profile.
     * GET /v2/users/:term/:id
     *
     * @return void
     */
    public function testV2GetAllDataFromUserAsStaff()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class, 'staff')->create();

        $this->asUser($admin, ['user', 'user:admin'])->get('v2/users/id/'.$user->id);
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name', 'last_name', 'facebook_id',
            ],
        ]);
    }

    /**
     * Test that retrieving a user as an admin returns full profile.
     * GET /v2/users/:term/:id
     *
     * @return void
     */
    public function testV2GetAllDataFromUserAsAdmin()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();

        $this->asUser($admin, ['user', 'user:admin'])->get('v2/users/id/'.$user->id);
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name', 'last_name', 'facebook_id',
            ],
        ]);
    }

    /**
     * Test that a staffer can update a user's profile.
     * GET /v2/users/:term/:id
     *
     * @return void
     */
    public function testV2UpdateProfileAsStaff()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v2/users/id/'.$user->id, [
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
        ]);

        $this->assertResponseStatus(200);

        // The user should remain unchanged.
        $user->fresh();
        $this->assertNotEquals('Alexander', $user->first_name);
        $this->assertNotEquals('Hamilton', $user->last_name);
    }

    /** @test */
    public function testV2UnsetFieldWithEmptyString()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v2/users/id/'.$user->id, [
            'mobile' => '',
        ]);

        $this->assertResponseStatus(200);

        // The user field should have been removed.
        $this->assertNull($user->fresh()->mobile);
    }

    /** @test */
    public function testV2UnsetFieldWithNull()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v2/users/id/'.$user->id, [
            'mobile' => null,
        ]);

        $this->assertResponseStatus(200);

        // The user field should have been removed.
        $this->assertNull($user->fresh()->mobile);
    }

    /**
     * Test that a staffer cannot change a user's role.
     * GET /v2/users/:term/:id
     *
     * @return void
     */
    public function testV2GrantRoleAsStaff()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v2/users/id/'.$user->id, [
            'role' => 'admin',
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that an admin can create a new user.
     * GET /v2/users/:term/:id
     *
     * @return void
     */
    public function testV2CreateUser()
    {
        $this->asAdminUser()->json('POST', 'v2/users', [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'email' => $this->faker->email,
            'country' => 'us',
        ]);

        $this->assertResponseStatus(201);
        $this->seeJsonSubset([
            'data' => [
                'first_name' => 'Hercules',
                'last_name' => 'Mulligan',
                'country' => 'US', // mutator should capitalize country codes!
            ],
        ]);
    }

    /**
     * Test that an admin can update a user's profile, including their role.
     * GET /v2/users/:term/:id
     *
     * @return void
     */
    public function testV2UpdateProfileAsAdmin()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();

        $this->asUser($admin, ['user', 'role:admin'])->json('PUT', 'v2/users/id/'.$user->id, [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'role' => 'admin',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'first_name' => 'Hercules',
                'last_name' => 'Mulligan',
                'role' => 'admin',
            ],
        ]);
    }

    /**
     * Test that creating a user results in saving normalized data.
     * POST /users
     *
     * @return void
     */
    public function testV2FieldsAreNormalized()
    {
        $this->asAdminUser()->json('POST', 'v2/users', [
            'first_name' => 'Batman',
            'email' => 'BatMan@example.com',
            'mobile' => '1 (222) 333-5555',
        ]);

        $this->assertResponseStatus(201);
        $this->seeInDatabase('users', [
            'first_name' => 'Batman',
            'email' => 'batman@example.com',
            'mobile' => '+12223335555',
        ]);
    }

    /**
     * Test that the `country` field is removed if it
     * does not contain a valid ISO-3166 country code.
     * GET /v2/users/:term/:id
     *
     * @return void
     */
    public function testV2SanitizesInvalidCountryCode()
    {
        $user = factory(User::class)->create([
            'email' => 'antonia.anderson@example.com',
            'country' => 'United States',
        ]);

        $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => 'antonia.anderson@example.com',
            'first_name' => 'Antonia',
        ]);

        // We should not see a validation error.
        $this->assertResponseStatus(200);

        // The user should be updated & their invalid country removed.
        $user = $user->fresh();
        $this->assertEquals('antonia.anderson@example.com', $user->email);
        $this->assertEquals(null, $user->country);
    }

    /**
     * Test that an admin can update a user's profile, including their role.
     * POST /v2/users/
     *
     * @return void
     */
    public function testV2UTF8Fields()
    {
        $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => 'woot-woot@example.com',
            'last_name' => '└(^o^)┘',
        ]);

        $this->assertResponseStatus(201);
        $this->seeJsonSubset([
            'data' => [
                'last_name' => '└(^o^)┘',
                'last_initial' => '└',
            ],
        ]);
    }

    /**
     * Test that ISO-8601 formatted date strings are accepted.
     * PUT /v2/users/id/:id
     *
     * @return void
     */
    public function testV2DateFields()
    {
        $user = factory(User::class)->create();

        $newTimestamp = '2017-11-02T18:42:00.000Z';
        $this->asAdminUser()->putJson('v2/users/id/'.$user->id, [
            'last_messaged_at' => $newTimestamp,
        ]);

        $this->assertResponseStatus(200);
        $this->assertEquals('2017-11-02T18:42:00+00:00', $user->fresh()->last_messaged_at->toIso8601String());
    }

    /**
     * Test that users get created_at & updated_at fields.
     * POST /v2/users/
     *
     * @return void
     */
    public function testV2SetCreatedAtField()
    {
        $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => 'alejandro@example.com',
        ]);

        $this->assertResponseStatus(201);

        // Let's see what 'created_at' is returned in the response
        $response = $this->decodeResponseJson();
        $date = new Carbon($response['data']['created_at']);

        // It should be today!
        $this->assertTrue($date->isSameDay(Carbon::now()));

        // And it should be stored as a ISODate in the actual database.
        $this->seeInDatabase('users', [
            'email' => 'alejandro@example.com',
            'created_at' => new MongoDB\BSON\UTCDateTime($date->getTimestamp() * 1000),
        ]);
    }

    /**
     * Test that we can only upsert with the ?upsert=true param
     * POST /v2/users/
     *
     * @return void
     */
    public function testV2UpsertRules()
    {
        $user = factory(User::class)->create([
            'first_name' => 'Daisy',
            'last_name' => 'Johnson',
            'source' => 'television',
            'source_detail' => 'agents-of-shield',
        ]);

        // Test that the user is returned without any changes.
        $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => $user->email,
            'first_name' => 'Daizy',
        ]);

        // It should return the unchanged user.
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'email' => $user->email,
                'first_name' => 'Daisy',
            ],
        ]);

        // Test that the user is returned with changes if ?upsert=true is present.
        $this->asAdminUser()->json('POST', 'v2/users?upsert=true', [
            'email' => $user->email,
            'first_name' => 'Daizy',
        ]);

        // It should return the upserted record.
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'email' => $user->email,
                'first_name' => 'Daizy',
            ],
        ]);
    }

    /**
     * Test that we can filter records with both ?search[email]=test@dosomething.org
     * and ?search=test@dosomething.org patterns
     * GET /v2/users/
     *
     * @return void
     */
    public function testV2FilterBySearchFieldParam()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);

        $this->withAccessToken(['admin'])->json('GET', 'v2/users?search[email]='.$user->email);
        $this->assertCount(1, $this->decodeResponseJson()['data']);
        $this->assertEquals($this->decodeResponseJson()['data'][0]['email'], $user->email);

        $this->withAccessToken(['admin'])->json('GET', 'v2/users?search='.$user->email);
        $this->assertCount(1, $this->decodeResponseJson()['data']);
        $this->assertEquals($this->decodeResponseJson()['data'][0]['email'], $user->email);
    }
}
