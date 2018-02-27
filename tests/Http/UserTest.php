<?php

use Carbon\Carbon;
use Northstar\Models\User;

class UserTest extends BrowserKitTestCase
{
    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testIndexNotVisibleToUserRole()
    {
        // Make a normal user to test acting as.
        $user = factory(User::class)->create();

        // Make some test users to see in the index.
        factory(User::class, 5)->create();

        $this->asUser($user, ['role:admin'])->get('v1/users');
        $this->assertResponseStatus(401);
    }

    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testIndexVisibleToStaffRole()
    {
        // Make a staff user & some test users.
        $staff = factory(User::class, 'staff')->create();
        factory(User::class, 5)->create();

        $this->asUser($staff, ['role:staff'])->get('v1/users');
        $this->assertResponseStatus(200);
    }

    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testIndexVisibleToAdminRole()
    {
        // Make a admin & some test users.
        $admin = factory(User::class, 'admin')->create();
        factory(User::class, 5)->create();

        $this->asUser($admin, ['role:admin'])->get('v1/users');
        $this->assertResponseStatus(200);
    }

    /**
     * Test that we can filter records by date range.
     * GET /v1/users/
     *
     * @return void
     */
    public function testFilterByDateField()
    {
        factory(User::class, 4)->create(['updated_at' => $this->faker->dateTimeBetween('1/1/2000', '12/31/2009')]);
        factory(User::class, 5)->create(['updated_at' => $this->faker->dateTimeBetween('1/1/2010', '1/1/2015')]);
        factory(User::class, 6)->create(['updated_at' => $this->faker->dateTimeBetween('1/2/2015', '1/1/2017')]);

        $this->withAccessToken(['admin'])->json('GET', 'v1/users?before[updated_at]=1/1/2010');
        $this->assertCount(4, $this->decodeResponseJson()['data'], 'can filter `updated_at` before timestamp');

        $this->withAccessToken(['admin'])->json('GET', 'v1/users?after[updated_at]=1/1/2015');
        $this->assertCount(6, $this->decodeResponseJson()['data'], 'can filter `updated_at` after timestamp');

        $this->withAccessToken(['admin'])->json('GET', 'v1/users?before[updated_at]=1/2/2015&after[updated_at]=12/31/2009');
        $this->assertCount(5, $this->decodeResponseJson()['data'], 'can filter `updated_at` between two timestamps');
    }

    /**
     * Test that retrieving a user as a non-admin returns limited profile.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetPublicDataFromUser()
    {
        $user = factory(User::class)->create();
        $viewer = factory(User::class)->create();

        // Test that we can view public profile as another user.
        $this->asUser($viewer, ['user', 'user:admin'])->get('v1/users/_id/'.$user->id);
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
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetAllDataFromUserAsStaff()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class, 'staff')->create();

        $this->asUser($admin, ['user', 'user:admin'])->get('v1/users/id/'.$user->id);
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
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetAllDataFromUserAsAdmin()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();

        $this->asUser($admin, ['user', 'user:admin'])->get('v1/users/id/'.$user->id);
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
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testUpdateProfileAsStaff()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v1/users/id/'.$user->id, [
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
    public function testUnsetFieldWithEmptyString()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v1/users/id/'.$user->id, [
            'mobile' => '',
        ]);

        $this->assertResponseStatus(200);

        // The user field should have been removed.
        $this->assertNull($user->fresh()->mobile);
    }

    /** @test */
    public function testUnsetFieldWithNull()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v1/users/id/'.$user->id, [
            'mobile' => null,
        ]);

        $this->assertResponseStatus(200);

        // The user field should have been removed.
        $this->assertNull($user->fresh()->mobile);
    }

    /**
     * Test that a staffer cannot change a user's role.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGrantRoleAsStaff()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v1/users/id/'.$user->id, [
            'role' => 'admin',
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that an admin can create a new user.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testCreateUser()
    {
        $this->asAdminUser()->json('POST', 'v1/users', [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'email' => $this->faker->email,
            'country' => 'us',
            'source' => 'historical',
            'source_detail' => 'american-revolution',
        ]);

        $this->assertResponseStatus(201);
        $this->seeJsonSubset([
            'data' => [
                'first_name' => 'Hercules',
                'last_name' => 'Mulligan',
                'country' => 'US', // mutator should capitalize country codes!
                'source' => 'historical',
                'source_detail' => 'american-revolution',
            ],
        ]);
    }

    /**
     * Test that an admin can update a user's profile, including their role.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testUpdateProfileAsAdmin()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();

        $this->asUser($admin, ['user', 'role:admin'])->json('PUT', 'v1/users/id/'.$user->id, [
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
    public function testFieldsAreNormalized()
    {
        $this->asAdminUser()->json('POST', 'v1/users', [
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
     * Test that we can still set old `mobilecommons_status` field.
     * POST /users
     *
     * @return void
     */
    public function testMobileCommonsStatusFieldTransform()
    {
        $this->asAdminUser()->json('POST', 'v1/users', [
            'mobile' => '1 (222) 333-5555',
            'mobilecommons_status' => 'active',
        ]);

        $this->assertResponseStatus(201);
        $this->seeInDatabase('users', [
            'mobile' => '+12223335555',
            'sms_status' => 'active',
        ]);
    }

    /**
     * Test that the `country` field is removed if it
     * does not contain a valid ISO-3166 country code.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testSanitizesInvalidCountryCode()
    {
        $user = factory(User::class)->create([
            'email' => 'antonia.anderson@example.com',
            'country' => 'United States',
        ]);

        $this->asAdminUser()->json('POST', 'v1/users', [
            'email' => 'antonia.anderson@example.com',
            'first_name' => 'Antonia',
        ]);

        // We should not see a validation error.
        $this->assertResponseStatus(200);

        // The user should be updated & their invalid country removed.
        $user = $user->fresh();
        $this->assertEquals('Antonia', $user->first_name);
        $this->assertEquals(null, $user->country);
    }

    /**
     * Test that the `country` field is validated.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testValidatesCountryCode()
    {
        $this->asAdminUser()->json('POST', 'v1/users', [
            'email' => 'american@example.com',
            'country' => 'united states',
        ]);

        $this->assertResponseStatus(422);

        $this->asAdminUser()->json('POST', 'v1/users', [
            'email' => 'american@example.com',
            'country' => 'us',
        ]);

        $this->assertResponseStatus(201);
    }

    /**
     * Test that an admin can update a user's profile, including their role.
     * POST /v1/users/
     *
     * @return void
     */
    public function testUTF8Fields()
    {
        $this->asAdminUser()->json('POST', 'v1/users', [
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
     * PUT /v1/users/id/:id
     *
     * @return void
     */
    public function testDateFields()
    {
        $user = factory(User::class)->create();

        $newTimestamp = '2017-11-02T18:42:00.000Z';
        $this->asAdminUser()->putJson('v1/users/id/'.$user->id, [
            'last_messaged_at' => $newTimestamp,
        ]);

        $this->assertResponseStatus(200);
        $this->assertEquals('2017-11-02T18:42:00+00:00', $user->fresh()->last_messaged_at->toIso8601String());
    }

    /**
     * Test that users get created_at & updated_at fields.
     * POST /v1/users/
     *
     * @return void
     */
    public function testSetCreatedAtField()
    {
        $this->asAdminUser()->json('POST', 'v1/users', [
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
     * Test that we can only upsert created_at to be earlier.
     * POST /v1/users/
     *
     * @return void
     */
    public function testCantUpsertCreatedAtField()
    {
        $user = factory(User::class)->create([
            'first_name' => 'Daisy',
            'last_name' => 'Johnson',
            'source' => 'television',
            'source_detail' => 'agents-of-shield',
        ]);

        // Well, we may have once wanted to backfill an earlier `created_at` but we don't now!!
        $this->asAdminUser()->json('POST', 'v1/users', [
            'email' => $user->email,
            'first_name' => 'Daisy',
            'created_at' => '1088640000', // first comic book appearance!
            'source' => 'comic',
            'source_detail' => 'secret-war/2',
        ]);

        // It should ignore the new `source, `source_detail`, and `created_at`.
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'email' => $user->email,
                'first_name' => 'Daisy',
                'source' => 'television',
                'source_detail' => 'agents-of-shield',
                'created_at' => $user->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Test that we can filter records with ?search[email]=test@dosomething.org pattern
     * GET /v1/users/
     *
     * @return void
     */
    public function testFilterBySearchFieldParam()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);
        $secondUser = factory(User::class)->create(['mobile' => $this->faker->phoneNumber]);

        $this->withAccessToken(['admin'])->json('GET', 'v1/users?search[email]=' . $user->email);
        $this->assertCount(1, $this->decodeResponseJson()['data']);
        $this->assertEquals($this->decodeResponseJson()['data'][0]['email'], $user->email);

        $this->withAccessToken(['admin'])->json('GET', 'v1/users?search[email]=' . $user->email . '&search[mobile]=' . $secondUser->mobile);

        $this->assertCount(2, $this->decodeResponseJson()['data']);
        $this->assertEquals($this->decodeResponseJson()['data'][0]['email'], $user->email);
        $this->assertEquals($this->decodeResponseJson()['data'][1]['mobile'], substr($secondUser->mobile, 2));
    }

    /**
     * Test that we can filter records with ?search=test@dosomething.org pattern
     * GET /v1/users/
     *
     * @return void
     */
    public function testFilterBySearchParamWithoutField()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);
        $secondUser = factory(User::class)->create(['mobile' => $this->faker->phoneNumber]);

        $this->withAccessToken(['admin'])->json('GET', 'v1/users?search=' . $user->email);
        $this->assertCount(1, $this->decodeResponseJson()['data']);
        $this->assertEquals($this->decodeResponseJson()['data'][0]['email'], $user->email);

        $this->withAccessToken(['admin'])->json('GET', 'v1/users?search=' . $user->email . ',' . $secondUser->mobile);
        dd($this->decodeResponseJson());
        $this->assertCount(2, $this->decodeResponseJson()['data']);
        $this->assertEquals($this->decodeResponseJson()['data'][0]['email'], $user->email);
        $this->assertEquals($this->decodeResponseJson()['data'][1]['mobile'], substr($secondUser->mobile, 2));
    }
}
