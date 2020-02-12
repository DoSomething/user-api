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

        $this->asUser($staff, ['role:staff', 'user'])->get('v2/users');
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

        $this->asUser($admin, ['role:admin', 'user'])->get('v2/users');
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

        $this->withAccessToken(['admin', 'user'])->json('GET', 'v2/users?before[updated_at]=1/1/2010');
        $this->assertCount(4, $this->decodeResponseJson()['data'], 'can filter `updated_at` before timestamp');

        $this->withAccessToken(['admin', 'user'])->json('GET', 'v2/users?after[updated_at]=1/1/2015');
        $this->assertCount(6, $this->decodeResponseJson()['data'], 'can filter `updated_at` after timestamp');

        $this->withAccessToken(['admin', 'user'])->json('GET', 'v2/users?before[updated_at]=1/2/2015&after[updated_at]=12/31/2009');
        $this->assertCount(5, $this->decodeResponseJson()['data'], 'can filter `updated_at` between two timestamps');
    }

    /**
     * Test that retrieving a user as a non-admin returns limited profile.
     * GET /v2/users/:id
     *
     * @return void
     */
    public function testV2GetPublicDataAsAnonymousUser()
    {
        $user = factory(User::class)->create();

        // Test that we can view public profile as another user.
        $this->get('v2/users/'.$user->id);
        $this->assertResponseStatus(200);

        // And test that private profile fields are hidden for the other user.
        $data = $this->decodeResponseJson()['data'];
        $this->assertArrayHasKey('first_name', $data);
        $this->assertArrayNotHasKey('last_name', $data);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('mobile', $data);
        $this->assertArrayNotHasKey('facebook_id', $data);
        $this->assertArrayNotHasKey('school_id', $data);
    }

    /**
     * Test that retrieving a user as a non-admin returns limited profile.
     * GET /v2/users/:id
     *
     * @return void
     */
    public function testV2GetPublicDataAsAuthenticatedUser()
    {
        $user = factory(User::class)->create([
            'first_name' => 'Puppet',
            'last_name' => 'Sloth',
        ]);
        $viewer = factory(User::class)->create();

        // Test that we can view public profile as another user.
        $this->asUser($viewer, ['user', 'user:admin'])->get('v2/users/'.$user->id);
        $this->assertResponseStatus(200);
        $this->seeJsonField('data.first_name', 'Puppet');
        $this->seeJsonField('data.display_name', 'Puppet S.');

        // And test that private profile fields are hidden for the other user.
        $this->dontSeeJsonField('data.last_name');
        $this->dontSeeJsonField('data.age');
        $this->dontSeeJsonField('data.email');
        $this->dontSeeJsonField('data.mobile');
        $this->dontSeeJsonField('data.facebook_id');
        $this->dontSeeJsonField('data.school_id');
    }

    /**
     * Test that retrieving a user as an admin returns full profile.
     * GET /v2/users/:id
     *
     * @return void
     */
    public function testV2GetAllDataFromUserAsStaff()
    {
        $this->mockTime('08/08/2019'); // ...so we can assert age!

        $user = factory(User::class)->create([
            'first_name' => 'Puppet',
            'last_name' => 'Sloth',
            'email' => 'puppet.sloth@dosomething.org',
            'mobile' => '+18602035512',
            'birthdate' => '01/01/1993',
        ]);

        $this->asStaffUser()->get('v2/users/'.$user->id);
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonField('data.first_name', 'Puppet');
        $this->seeJsonField('data.display_name', 'Puppet S.');
        $this->seeJsonField('data.last_name', 'Sloth');
        $this->seeJsonField('data.email', 'puppet.sloth@dosomething.org');
        $this->seeJsonField('data.email_preview', 'pup...@dosomething.org');
        $this->seeJsonField('data.mobile', '8602035512'); // @TODO: This should be E.164!
        $this->seeJsonField('data.mobile_preview', '(860) 203-XXXX');
        $this->seeJsonField('data.school_id', '12500012');
        $this->seeJsonField('data.school_id_preview', '125XXXXX');
    }

    /**
     * Test that retrieving a user as an admin returns full profile.
     * GET /v2/users/:id
     *
     * @return void
     */
    public function testV2GetAllDataFromUserAsAdmin()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();

        $this->asUser($admin, ['user', 'user:admin'])->get('v2/users/'.$user->id);
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name', 'last_name', 'facebook_id', 'school_id',
            ],
        ]);
    }

    /**
     * Test that normal users can't request optional fields.
     * GET /v2/users/:id
     *
     * @return void
     */
    public function testV2GetOptionalFieldsFromUserAsNormalUser()
    {
        config(['features.optional-fields' => true]);

        $user = factory(User::class)->create();

        // Normal users should not be able to query sensitive values for others:
        $this->asNormalUser()->get('v2/users/'.$user->id.'?include=email,addr_street1,school_id');
        $this->assertResponseOk()
            ->dontSeeJsonField('data.email')
            ->dontSeeJsonField('data.addr_street1')
            ->dontSeeJsonField('data.school_id');
    }

    /**
     * Test that staffers can request optional fields.
     * GET /v2/users/:id
     *
     * @return void
     */
    public function testV2GetOptionalFieldsFromUserAsAdmin()
    {
        config(['features.optional-fields' => true]);

        $user = factory(User::class)->create();

        // Check that sensitive fields are not included by default:
        $this->asStaffUser()->get('v2/users/'.$user->id);
        $this->assertResponseOk()->dontSeeJsonField('data.email');

        // When we re-query with `?include=`, we should see them!
        $this->asStaffUser()->get('v2/users/'.$user->id.'?include=email,addr_street1,school_id');
        $this->assertResponseOk()
            ->seeJsonField('data.email', $user->email)
            ->seeJsonField('data.addr_street1', $user->addr_street1)
            ->seeJsonField('data.school_id', $user->school_id);
    }

    /**
     * Test that a staffer can update a user's profile.
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testV2UpdateProfileAsStaff()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff', 'write'])->json('PUT', 'v2/users/'.$user->id, [
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
        ]);

        $this->assertResponseStatus(200);

        // The user should be updated.
        $this->seeInDatabase('users', [
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
            '_id' => $user->id,
        ]);
    }

    /**
     * Test that a user can update their own profile.
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testV2UpdateProfileAsSelf()
    {
        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->json('PUT', 'v2/users/'.$user->id, [
            'first_name' => 'Pepper',
            'last_name' => 'Puppy',
            'school_id' => '7110001',
        ]);

        $this->assertResponseStatus(200);

        // The user should be updated.
        $this->seeInDatabase('users', [
            'first_name' => 'Pepper',
            'last_name' => 'Puppy',
            '_id' => $user->id,
            'school_id' => '7110001',
        ]);
    }

    /**
     * Test that an update does not add the "badges" feature flag.
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testV2UpdateShouldNotAddBadgesFlag()
    {
        // Turn on the badge test feature flag
        config(['features.badges' => true]);

        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->json('PUT', 'v2/users/'.$user->id, [
            'first_name' => 'Pepper',
            'last_name' => 'Puppy',
        ]);

        $this->assertResponseStatus(200);

        // Should not see the badges feature flag.
        $user->refresh();
        $this->assertNull($user->feature_flags);
    }

    /**
     * Test that a user cannot update another user's profile.
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testV2UpdateProfileAsOther()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        $this->asUser($user2, ['user', 'role:staff', 'write'])->json('PUT', 'v2/users/'.$user1->id, [
            'first_name' => 'Burt',
            'last_name' => 'Macklin',
        ]);

        $this->assertResponseStatus(401);

        // The user should be updated.
        $this->seeInDatabase('users', [
            'first_name' => $user1->first_name,
            'last_name' => $user1->last_name,
            '_id' => $user1->id,
        ]);
    }

    /**
     * Test that a machine can update a user's profile.
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testV2UpdateProfileAsMachine()
    {
        $user = factory(User::class)->create();

        $this->asMachine()->json('PUT', 'v2/users/'.$user->id, [
            'first_name' => 'Wilhelmina',
            'last_name' => 'Grubbly-Plank',
            'school_id' => '11122019',
        ]);

        $this->assertResponseStatus(200);

        // The user should be updated.
        $this->seeInDatabase('users', [
            'first_name' => 'Wilhelmina',
            'last_name' => 'Grubbly-Plank',
            '_id' => $user->id,
            'school_id' => '11122019',
        ]);
    }

    /**
     * Test that the write scope is required to update a profile.
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testV2RequiredWriteScopeToUpdateProfile()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $response = $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v2/users/'.$user->id, [
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
        ]);

        $this->assertResponseStatus(401);
        $this->assertEquals('Requires the `write` scope.', $response->decodeResponseJson()['hint']);
    }

    /** @test */
    public function testV2UnsetFieldWithEmptyString()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff', 'write'])->json('PUT', 'v2/users/'.$user->id, [
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

        $this->asUser($staff, ['user', 'role:staff', 'write'])->json('PUT', 'v2/users/'.$user->id, [
            'mobile' => null,
        ]);

        $this->assertResponseStatus(200);

        // The user field should have been removed.
        $this->assertNull($user->fresh()->mobile);
    }

    /**
     * Test that a staffer cannot change a user's role.
     * GET /v2/users/:id
     *
     * @return void
     */
    public function testV2GrantRoleAsStaff()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v2/users/'.$user->id, [
            'role' => 'admin',
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that an admin can create a new user.
     * POST /v2/users
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
     * Test that the write scope is required to create a new user.
     * POST /v2/users
     *
     * @return void
     */
    public function testV2RequiredWriteScopeCreateUser()
    {
        $user = factory(User::class, 'staff')->create();

        $response = $this->asUser($user, ['user', 'role:staff'])->json('POST', 'v2/users', [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'email' => $this->faker->email,
            'country' => 'us',
        ]);

        $this->assertResponseStatus(401);
        $this->assertEquals('Requires the `write` scope.', $response->decodeResponseJson()['hint']);
    }

    /**
     * Test that an admin can update a user's profile, including their role.
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testV2UpdateProfileAsAdmin()
    {
        $user = factory(User::class)->create();

        $this->asAdminUser()->json('PUT', 'v2/users/'.$user->id, [
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
     * Test that the `country` field is validated.
     * GET /users/:id
     *
     * @return void
     */
    public function testV2ValidatesCountryCode()
    {
        $this->asAdminUser()->json('POST', 'v2/users', [
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
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testV2DateFields()
    {
        $user = factory(User::class)->create();

        $newTimestamp = '2017-11-02T18:42:00.000Z';
        $this->asAdminUser()->putJson('v2/users/'.$user->id, [
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

        // Test that an exception is thrown if the user exists.
        $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => $user->email,
            'first_name' => 'Daizy',
        ]);

        // It should return a 422 error.
        $this->assertResponseStatus(422);
        $this->assertEquals($this->decodeResponseJson()['error']['fields']['id'][0], 'A record matching one of the given indexes already exists.');

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

        $this->withAccessToken(['admin', 'user'])->json('GET', 'v2/users?search[email]='.$user->email);
        $this->assertCount(1, $this->decodeResponseJson()['data']);
        $this->assertEquals($this->decodeResponseJson()['data'][0]['email'], $user->email);

        $this->withAccessToken(['admin', 'user'])->json('GET', 'v2/users?search='.$user->email);
        $this->assertCount(1, $this->decodeResponseJson()['data']);
        $this->assertEquals($this->decodeResponseJson()['data'][0]['email'], $user->email);
    }

    /**
     * Test that retrieving a user by mobile as a non-admin returns a 401 error code.
     * GET /v2/mobile/:mobile
     *
     * @return void
     */
    public function testV2GetDataFromUserByMobileAsAnonUser()
    {
        $user = factory(User::class)->create(['mobile' => $this->faker->phoneNumber]);
        $viewer = factory(User::class)->create();

        // Test that we can view user information if not staff or admin.
        $this->asUser($viewer, ['user', 'role:staff'])->get('v2/mobile/'.$user->mobile);
        $this->assertResponseStatus(401);
        $this->assertEquals('The resource owner or authorization server denied the request.', $this->decodeResponseJson()['message']);
    }

    /**
     * Test that retrieving a user by mobile as staff returns full profile.
     * GET /v2/mobile/:mobile
     *
     * @return void
     */
    public function testV2GetAllDataFromUserByMobileAsStaff()
    {
        $user = factory(User::class)->create(['mobile' => $this->faker->phoneNumber]);
        $admin = factory(User::class, 'staff')->create();

        $this->asUser($admin, ['role:staff'])->get('v2/mobile/'.$user->mobile);
        $this->assertResponseStatus(200);

        // Check that fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name', 'last_name', 'facebook_id',
            ],
        ]);
    }

    /**
     * Test that retrieving a user by mobile as an admin returns full profile.
     * GET /v2/mobile/:mobile
     *
     * @return void
     */
    public function testV2GetAllDataFromUserMobileAsAdmin()
    {
        $user = factory(User::class)->create(['mobile' => $this->faker->phoneNumber]);
        $admin = factory(User::class, 'admin')->create();

        $this->asUser($admin, ['user', 'role:admin'])->get('v2/mobile/'.$user->mobile);
        $this->assertResponseStatus(200);

        // Check that fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name', 'last_name', 'facebook_id',
            ],
        ]);
    }

    /**
     * Test that retrieving a user by email as a non-admin returns a 401 response.
     * GET /v2/email/:email
     *
     * @return void
     */
    public function testV2GetDataFromUserByEmailAsAnonUser()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);
        $viewer = factory(User::class)->create();

        // Test that we cannot view public profile as another user.
        $this->asUser($viewer, ['user', 'user:admin'])->get('v2/email/'.$user->email);
        $this->assertResponseStatus(401);
        $this->assertEquals('The resource owner or authorization server denied the request.', $this->decodeResponseJson()['message']);
    }

    /**
     * Test that retrieving a user by email as staff returns full profile.
     * GET /v2/email/:email
     *
     * @return void
     */
    public function testV2GetAllDataFromUserByEmailAsStaff()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);
        $admin = factory(User::class, 'staff')->create();

        $this->asUser($admin, ['role:staff'])->get('v2/email/'.$user->email);
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name', 'last_name', 'facebook_id',
            ],
        ]);
    }

    /**
     * Test that retrieving a user by email as an admin returns full profile.
     * GET /v2/email/:email
     *
     * @return void
     */
    public function testV2GetAllDataFromUserEmailAsAdmin()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);
        $admin = factory(User::class, 'admin')->create();

        $this->asUser($admin, ['user', 'role:admin'])->get('v2/email/'.$user->email);
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name', 'last_name', 'facebook_id',
            ],
        ]);
    }

    /**
     * Test that write scope is required to delete a user.
     * DELETE /v2/email/:email
     *
     * @return void
     */
    public function testV2RequiredWriteScopeDeleteUser()
    {
        $user = factory(User::class, 'staff')->create();
        $userToDelete = factory(User::class)->create();

        $response = $this->asUser($user, ['user', 'role:staff'])->json('DELETE', 'v2/users/'.$userToDelete->id, [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'email' => $this->faker->email,
            'country' => 'us',
        ]);

        $this->assertResponseStatus(401);
        $this->assertEquals('Requires the `write` scope.', $response->decodeResponseJson()['hint']);
    }

    /**
     * Test that admin can delete a user.
     * DELETE /v2/email/:email
     *
     * @return void
     */
    public function testV2AdminCanDeleteUser()
    {
        $userToDelete = factory(User::class)->create();

        $response = $this->asAdminUser()->json('DELETE', 'v2/users/'.$userToDelete->id, [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'email' => $this->faker->email,
            'country' => 'us',
        ]);

        $this->assertResponseStatus(200);
    }

    /**
     * Test that an admin cannot add duplicates to email_subscription_topics.
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testV2UpdateEmailSubscriptionTopicsWithNoDupesAsAdmin()
    {
        $user = factory(User::class)->create();

        $this->asAdminUser()->json('PUT', 'v2/users/'.$user->id, [
            'email_subscription_topics' => ['news', 'news'],
        ]);

        $this->assertResponseStatus(200);

        // The email_subscription_topics should be updated with no duplicates
        $this->seeInDatabase('users', [
            '_id' => $user->id,
            'email_subscription_topics' => ['news'],
        ]);
    }

    /**
     * Test that user email_subcription_status is set to true if adding email_subscription_topics.
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testEmailSubscriptionStatusWhenAddingTopics()
    {
        $nullStatusUser = factory(User::class)->create([
            'email_subscription_topics' => null,
        ]);

        $this->asUser($nullStatusUser, ['user', 'write'])->json('PUT', 'v2/users/'.$nullStatusUser->id, [
            'email_subscription_topics' => ['news'],
        ]);

        $this->seeInDatabase('users', [
            '_id' => $nullStatusUser->id,
            'email_subscription_status' => true,
        ]);

        $unsubscribeStatusUser = factory(User::class)->create([
            'email_subscription_topics' => false,
        ]);

        $this->asUser($unsubscribeStatusUser, ['user', 'write'])->json('PUT', 'v2/users/'.$unsubscribeStatusUser->id, [
            'email_subscription_topics' => ['news'],
        ]);

        $this->seeInDatabase('users', [
            '_id' => $unsubscribeStatusUser->id,
            'email_subscription_status' => true,
        ]);
    }

    /**
     * Test that user email_subcription_status remains true if unsetting email_subscription_topics.
     * PUT /v2/users/:id
     *
     * @return void
     */
    public function testEmailSubscriptionStatusWhenClearingTopics()
    {
        // Our user factory creates an email subscriber with topic(s) by default. 
        $subscribedUser = factory(User::class)->create();

        $this->asUser($subscribedUser, ['user', 'write'])->json('PUT', 'v2/users/'.$subscribedUser->id, [
            'email_subscription_topics' => null,
        ]);

        $this->seeInDatabase('users', [
            '_id' => $subscribedUser->id,
            'email_subscription_status' => true,
        ]);
    }
}
