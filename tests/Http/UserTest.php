<?php

namespace Tests\Http;

use App\Models\Post;
use App\Models\Signup;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * Test retrieving multiple users.
     *
     * @return void
     */
    public function testV2IndexNotVisibleToUserRole()
    {
        // Make a normal user to test acting as.
        $user = factory(User::class)->create();

        // Make some test users to see in the index.
        factory(User::class, 5)->create();

        $response = $this->asUser($user, ['role:admin'])->get('v2/users');

        $response->assertStatus(401);
    }

    /**
     * Test retrieving multiple users.
     *
     * @return void
     */
    public function testV2IndexVisibleToStaffRole()
    {
        // Make a staff user & some test users.
        $staff = factory(User::class)->states('staff')->create();

        factory(User::class, 5)->create();

        $response = $this->asUser($staff, ['role:staff', 'user'])->get(
            'v2/users',
        );

        $response->assertStatus(200);
    }

    /**
     * Test retrieving multiple users.
     *
     * @return void
     */
    public function testV2IndexVisibleToAdminRole()
    {
        // Make a admin & some test users.
        $admin = factory(User::class)->states('admin')->create();

        factory(User::class, 5)->create();

        $response = $this->asUser($admin, ['role:admin', 'user'])->get(
            'v2/users',
        );

        $response->assertStatus(200);
    }

    /**
     * Test that we can filter records by date range.
     *
     * @return void
     */
    public function testV2FilterByDateField()
    {
        factory(User::class, 4)->create([
            'updated_at' => $this->faker->dateTimeBetween(
                '1/1/2000',
                '12/31/2009',
            ),
        ]);

        factory(User::class, 5)->create([
            'updated_at' => $this->faker->dateTimeBetween(
                '1/1/2010',
                '1/1/2015',
            ),
        ]);

        factory(User::class, 6)->create([
            'updated_at' => $this->faker->dateTimeBetween(
                '1/2/2015',
                '1/1/2017',
            ),
        ]);

        $responseOne = $this->withAccessToken(['admin', 'user'])->json(
            'GET',
            'v2/users?before[updated_at]=1/1/2010',
        );

        $this->assertCount(
            4,
            $responseOne->decodeResponseJson('data'),
            'can filter `updated_at` before timestamp',
        );

        $responseTwo = $this->withAccessToken(['admin', 'user'])->json(
            'GET',
            'v2/users?after[updated_at]=1/1/2015',
        );

        $this->assertCount(
            6,
            $responseTwo->decodeResponseJson('data'),
            'can filter `updated_at` after timestamp',
        );

        $responseThree = $this->withAccessToken(['admin', 'user'])->json(
            'GET',
            'v2/users?before[updated_at]=1/2/2015&after[updated_at]=12/31/2009',
        );

        $this->assertCount(
            5,
            $responseThree->decodeResponseJson('data'),
            'can filter `updated_at` between two timestamps',
        );
    }

    /**
     * Test that retrieving a user as a non-admin returns limited profile.
     *
     * @return void
     */
    public function testV2GetPublicDataAsAnonymousUser()
    {
        $user = factory(User::class)->create();

        // Test that we can view public profile as another user.
        $response = $this->get('v2/users/' . $user->id);

        $response->assertStatus(200);

        // And test that private profile fields are hidden for the other user.
        $data = $response->decodeResponseJson('data');

        $this->assertArrayHasKey('first_name', $data);
        $this->assertArrayNotHasKey('last_name', $data);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('mobile', $data);
        $this->assertArrayNotHasKey('facebook_id', $data);
        $this->assertArrayNotHasKey('school_id', $data);
        $this->assertArrayNotHasKey('club_id', $data);
    }

    /**
     * Test that retrieving a user as a non-admin returns limited profile.
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
        $response = $this->asUser($viewer, ['user', 'user:admin'])->get(
            'v2/users/' . $user->id,
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.first_name', 'Puppet');
        $response->assertJsonPath('data.display_name', 'Puppet S.');

        // And test that private profile fields are hidden for the other user.
        $response->assertJsonMissingExact(['data' => 'last_name']);
        $response->assertJsonMissingExact(['data' => 'age']);
        $response->assertJsonMissingExact(['data' => 'email']);
        $response->assertJsonMissingExact(['data' => 'mobile']);
        $response->assertJsonMissingExact(['data' => 'facebook_id']);
        $response->assertJsonMissingExact(['data' => 'school_id']);
        $response->assertJsonMissingExact(['data' => 'club_id']);
    }

    /**
     * Test that retrieving a user as an admin returns full profile.
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
            'referrer_user_id' => '559442cca59dbfca578b4bed',
            'school_id' => '12500012',
        ]);

        $response = $this->asStaffUser()->get('v2/users/' . $user->id);

        $response->assertStatus(200);

        // Check that public & private profile fields are visible
        $response->assertJsonPath('data.first_name', 'Puppet');
        $response->assertJsonPath('data.display_name', 'Puppet S.');
        $response->assertJsonPath('data.last_name', 'Sloth');
        $response->assertJsonPath('data.email', 'puppet.sloth@dosomething.org');
        $response->assertJsonPath(
            'data.email_preview',
            'pup...@dosomething.org',
        );
        $response->assertJsonPath('data.mobile', '8602035512'); // @TODO: This should be E.164!
        $response->assertJsonPath('data.mobile_preview', '(860) 203-XXXX');
        $response->assertJsonPath('data.school_id', '12500012');
        $response->assertJsonPath('data.school_id_preview', '125XXXXX');
        $response->assertJsonPath('data.club_id', 1);
        $response->assertJsonPath(
            'data.referrer_user_id',
            '559442cca59dbfca578b4bed',
        );
    }

    /**
     * Test that retrieving a user as an admin returns full profile.
     *
     * @return void
     */
    public function testV2GetAllDataFromUserAsAdmin()
    {
        $user = factory(User::class)->create();

        $admin = factory(User::class)->states('admin')->create();

        $response = $this->asUser($admin, ['user', 'user:admin'])->get(
            'v2/users/' . $user->id,
        );

        $response->assertStatus(200);

        // Check that public & private profile fields are visible
        $response->assertJsonStructure([
            'data' => [
                'id',
                'email',
                'first_name',
                'last_name',
                'facebook_id',
                'school_id',
                'club_id',
            ],
        ]);
    }

    /**
     * Test that normal users can't request optional fields.
     *
     * @return void
     */
    public function testV2GetOptionalFieldsFromUserAsNormalUser()
    {
        config(['features.optional-fields' => true]);

        $user = factory(User::class)->create();

        // Normal users should not be able to query sensitive values for others:
        $response = $this->asNormalUser()->get(
            'v2/users/' . $user->id . '?include=email,addr_street1,school_id',
        );

        $response->assertStatus(200);
        $response->assertJsonMissingExact(['data' => 'email']);
        $response->assertJsonMissingExact(['data' => 'addr_street1']);
        $response->assertJsonMissingExact(['data' => 'school_id']);
    }

    /**
     * Test that staffers can request optional fields.
     *
     * @return void
     */
    public function testV2GetOptionalFieldsFromUserAsAdmin()
    {
        config(['features.optional-fields' => true]);

        $user = factory(User::class)->create();

        // Check that sensitive fields are not included by default:
        $responseOne = $this->asStaffUser()->get('v2/users/' . $user->id);

        $responseOne->assertStatus(200);
        $responseOne->assertJsonMissingExact(['data' => 'email']);

        // When we re-query with `?include=`, we should see them!
        $responseTwo = $this->asStaffUser()->get(
            'v2/users/' . $user->id . '?include=email,addr_street1,school_id',
        );

        $responseTwo->assertStatus(200);
        $responseTwo->assertJsonPath('data.email', $user->email);
        $responseTwo->assertJsonPath('data.addr_street1', $user->addr_street1);
        $responseTwo->assertJsonPath('data.school_id', $user->school_id);
    }

    /**
     * Test that a staffer can update a user's profile.
     *
     * @return void
     */
    public function testV2UpdateProfileAsStaff()
    {
        $user = factory(User::class)->create();

        $staff = factory(User::class)->states('staff')->create();

        $response = $this->asUser($staff, [
            'user',
            'role:staff',
            'write',
        ])->json('PUT', 'v2/users/' . $user->id, [
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
            'club_id' => 2,
        ]);

        $response->assertStatus(200);

        // The user should be updated.
        $this->assertMongoDatabaseHas('users', [
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
            'club_id' => 2,
            '_id' => $user->id,
        ]);
    }

    /**
     * Test that a user can update their own profile.
     *
     * @return void
     */
    public function testV2UpdateProfileAsSelf()
    {
        $user = factory(User::class)->create();

        $response = $this->asUser($user, ['user', 'write'])->json(
            'PUT',
            'v2/users/' . $user->id,
            [
                'first_name' => 'Pepper',
                'last_name' => 'Puppy',
                'school_id' => '7110001',
                'club_id' => 2,
            ],
        );

        $response->assertStatus(200);

        // The user should be updated.
        $this->assertMongoDatabaseHas('users', [
            'first_name' => 'Pepper',
            'last_name' => 'Puppy',
            '_id' => $user->id,
            'school_id' => '7110001',
            'club_id' => 2,
        ]);
    }

    /**
     * Test that an update does not add the "badges" feature flag.
     *
     * @return void
     */
    public function testV2UpdateShouldNotAddBadgesFlag()
    {
        // Turn on the badge test feature flag
        config(['features.badges' => true]);

        $user = factory(User::class)->create();

        $response = $this->asUser($user, ['user', 'write'])->json(
            'PUT',
            'v2/users/' . $user->id,
            [
                'first_name' => 'Pepper',
                'last_name' => 'Puppy',
            ],
        );

        $response->assertStatus(200);

        // Should not see the badges feature flag.
        $user->refresh();

        $this->assertNull($user->feature_flags);
    }

    /**
     * Test that a user cannot update another user's profile.
     *
     * @return void
     */
    public function testV2UpdateProfileAsOther()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        $response = $this->asUser($user2, [
            'user',
            'role:staff',
            'write',
        ])->json('PUT', 'v2/users/' . $user1->id, [
            'first_name' => 'Burt',
            'last_name' => 'Macklin',
        ]);

        $response->assertStatus(401);

        // The user should be updated.
        $this->assertMongoDatabaseHas('users', [
            'first_name' => $user1->first_name,
            'last_name' => $user1->last_name,
            '_id' => $user1->id,
        ]);
    }

    /**
     * Test that a machine can update a user's profile.
     *
     * @return void
     */
    public function testV2UpdateProfileAsMachine()
    {
        $user = factory(User::class)->create();

        $response = $this->asMachine()->json('PUT', 'v2/users/' . $user->id, [
            'first_name' => 'Wilhelmina',
            'last_name' => 'Grubbly-Plank',
            'school_id' => '11122019',
            'club_id' => 2,
            'referrer_user_id' => '5e7aa023fdce2754fc584dea',
        ]);

        $response->assertStatus(200);

        // The user should be updated.
        $this->assertMongoDatabaseHas('users', [
            'first_name' => 'Wilhelmina',
            'last_name' => 'Grubbly-Plank',
            '_id' => $user->id,
            'school_id' => '11122019',
            'club_id' => 2,
            'referrer_user_id' => '5e7aa023fdce2754fc584dea',
        ]);
    }

    /**
     * Test that the write scope is required to update a profile.
     *
     * @return void
     */
    public function testV2RequiredWriteScopeToUpdateProfile()
    {
        $user = factory(User::class)->create();

        $staff = factory(User::class)->states('staff')->create();

        $response = $this->asUser($staff, ['user', 'role:staff'])->json(
            'PUT',
            'v2/users/' . $user->id,
            [
                'first_name' => 'Alexander',
                'last_name' => 'Hamilton',
            ],
        );

        $response->assertStatus(401);

        $this->assertEquals(
            'Requires the `write` scope.',
            $response->decodeResponseJson('hint'),
        );
    }

    /** @test */
    public function testV2UnsetFieldWithEmptyString()
    {
        $user = factory(User::class)->create();

        $staff = factory(User::class)->states('staff')->create();

        $response = $this->asUser($staff, [
            'user',
            'role:staff',
            'write',
        ])->json('PUT', 'v2/users/' . $user->id, [
            'mobile' => '',
        ]);

        $response->assertStatus(200);

        // The user field should have been removed.
        $this->assertNull($user->fresh()->mobile);
    }

    /** @test */
    public function testV2UnsetFieldWithNull()
    {
        $user = factory(User::class)->create();

        $staff = factory(User::class)->states('staff')->create();

        $response = $this->asUser($staff, [
            'user',
            'role:staff',
            'write',
        ])->json('PUT', 'v2/users/' . $user->id, [
            'mobile' => null,
        ]);

        $response->assertStatus(200);

        // The user field should have been removed.
        $this->assertNull($user->fresh()->mobile);
    }

    /**
     * Test that a staffer cannot change a user's role.
     *
     * @return void
     */
    public function testV2GrantRoleAsStaff()
    {
        $user = factory(User::class)->create();

        $staff = factory(User::class)->states('staff')->create();

        $response = $this->asUser($staff, ['user', 'role:staff'])->json(
            'PUT',
            'v2/users/' . $user->id,
            [
                'role' => 'admin',
            ],
        );

        $response->assertStatus(401);
    }

    /**
     * Test that an admin can create a new user.
     *
     * @return void
     */
    public function testV2CreateUser()
    {
        $response = $this->asAdminUser()->json('POST', 'v2/users', [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'email' => $this->faker->email,
            'country' => 'us',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.first_name', 'Hercules');
        $response->assertJsonPath('data.last_name', 'Mulligan');
        $response->assertJsonPath('data.country', 'US'); // mutator should capitalize country codes!
        $response->assertJsonPath('data.badges', []);
    }

    /**
     * Test that the write scope is required to create a new user.
     *
     * @return void
     */
    public function testV2RequiredWriteScopeCreateUser()
    {
        $user = factory(User::class)->states('staff')->create();

        $response = $this->asUser($user, ['user', 'role:staff'])->json(
            'POST',
            'v2/users',
            [
                'first_name' => 'Hercules',
                'last_name' => 'Mulligan',
                'email' => $this->faker->email,
                'country' => 'us',
            ],
        );

        $response->assertStatus(401);

        $this->assertEquals(
            'Requires the `write` scope.',
            $response->decodeResponseJson()['hint'],
        );
    }

    /**
     * Test that an admin can update a user's profile, including their role.
     *
     * @return void
     */
    public function testV2UpdateProfileAsAdmin()
    {
        $user = factory(User::class)->create();

        $response = $this->asAdminUser()->json('PUT', 'v2/users/' . $user->id, [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'role' => 'admin',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.first_name', 'Hercules');
        $response->assertJsonPath('data.last_name', 'Mulligan');
        $response->assertJsonPath('data.role', 'admin');
    }

    /**
     * Test that creating a user results in saving normalized data.
     *
     * @return void
     */
    public function testV2FieldsAreNormalized()
    {
        $response = $this->asAdminUser()->json('POST', 'v2/users', [
            'first_name' => 'Batman',
            'email' => 'BatMan@example.com',
            'mobile' => '1 (222) 333-5555',
        ]);

        $response->assertStatus(201);

        $this->assertMongoDatabaseHas('users', [
            'first_name' => 'Batman',
            'email' => 'batman@example.com',
            'mobile' => '+12223335555',
        ]);
    }

    /**
     * Test that the `country` field is validated.
     *
     * @return void
     */
    public function testV2ValidatesCountryCode()
    {
        $responseOne = $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => 'american@example.com',
            'country' => 'united states',
        ]);

        $responseOne->assertStatus(422);

        $responseTwo = $this->asAdminUser()->json('POST', 'v1/users', [
            'email' => 'american@example.com',
            'country' => 'us',
        ]);

        $responseTwo->assertStatus(201);
    }

    /**
     * Test that an admin can update a user's profile, including their role.
     *
     * @return void
     */
    public function testV2UTF8Fields()
    {
        $response = $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => 'woot-woot@example.com',
            'last_name' => '└(^o^)┘',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.last_name', '└(^o^)┘');
        $response->assertJsonPath('data.last_initial', '└');
    }

    /**
     * Test that ISO-8601 formatted date strings are accepted.
     *
     * @return void
     */
    public function testV2DateFields()
    {
        $user = factory(User::class)->create();

        $newTimestamp = '2017-11-02T18:42:00.000Z';

        $response = $this->asAdminUser()->putJson('v2/users/' . $user->id, [
            'last_messaged_at' => $newTimestamp,
        ]);

        $response->assertStatus(200);

        $this->assertEquals(
            '2017-11-02T18:42:00+00:00',
            $user->fresh()->last_messaged_at->toIso8601String(),
        );
    }

    /**
     * Test that the `sms_subscription_topics` field is validated.
     *
     * @return void
     */
    public function testV2ValidatesSmsSubscriptionTopics()
    {
        $responseOne = $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => 'test@example.com',
            'sms_subscription_topics' => ['bugs'],
        ]);

        $responseOne->assertStatus(422);

        $responseTwo = $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => 'test@example.com',
            'sms_subscription_topics' => 'bugs',
        ]);

        $responseTwo->assertStatus(500);

        $responseThree = $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => 'test@example.com',
            'sms_subscription_topics' => ['voting'],
        ]);

        $responseThree->assertStatus(201);
    }

    /**
     * Test that the `mobile` field is validated.
     *
     * @return void
     */
    public function testV2ValidatesMobile()
    {
        $responseOne = $this->asAdminUser()->json('POST', 'v2/users', [
            'mobile' => '000-00-0000',
        ]);

        $responseOne->assertStatus(422);

        $responseTwo = $this->asAdminUser()->json('POST', 'v2/users', [
            'mobile' => '212-254-2390',
        ]);

        $responseTwo->assertStatus(201);
    }

    /**
     * Test that the `club_id` field is validated.
     *
     * @return void
     */
    public function testV2ValidatesClubId()
    {
        $responseOne = $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => 'test@example.com',
            'club_id' => 'something bad',
        ]);

        $responseOne->assertStatus(422);

        $responseTwo = $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => 'test@example.com',
            'club_id' => 1,
        ]);

        $responseTwo->assertStatus(201);
    }

    /**
     * Test that we can only upsert with the ?upsert=true param.
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
        $responseOne = $this->asAdminUser()->json('POST', 'v2/users', [
            'email' => $user->email,
            'first_name' => 'Daizy',
        ]);

        // It should return a 422 error.
        $responseOne->assertStatus(422);

        $this->assertEquals(
            $responseOne->decodeResponseJson('error.fields.id.0'),
            'A record matching one of the given indexes already exists.',
        );

        // Test that the user is returned with changes if ?upsert=true is present.
        $responseTwo = $this->asAdminUser()->json(
            'POST',
            'v2/users?upsert=true',
            [
                'email' => $user->email,
                'first_name' => 'Daizy',
            ],
        );

        // It should return the upserted record.
        $responseTwo->assertStatus(200);

        $responseTwo->assertJsonPath('data.email', $user->email);
        $responseTwo->assertJsonPath('data.first_name', 'Daizy');
    }

    /**
     * Test that we can filter records with both ?search[email]=test@dosomething.org
     * and ?search=test@dosomething.org patterns.
     *
     * @return void
     */
    public function testV2FilterBySearchFieldParam()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);

        $responseOne = $this->withAccessToken(['admin', 'user'])->json(
            'GET',
            'v2/users?search[email]=' . $user->email,
        );

        $this->assertCount(1, $responseOne->decodeResponseJson('data'));
        $this->assertEquals(
            $responseOne->decodeResponseJson('data.0.email'),
            $user->email,
        );

        $responseTwo = $this->withAccessToken(['admin', 'user'])->json(
            'GET',
            'v2/users?search=' . $user->email,
        );

        $this->assertCount(1, $responseTwo->decodeResponseJson('data'));
        $this->assertEquals(
            $responseTwo->decodeResponseJson('data.0.email'),
            $user->email,
        );
    }

    /**
     * Test that retrieving a user by mobile as a non-admin returns a 401 error code.
     *
     * @return void
     */
    public function testV2GetDataFromUserByMobileAsAnonUser()
    {
        $user = factory(User::class)->create([
            'mobile' => $this->faker->phoneNumber,
        ]);

        $viewer = factory(User::class)->create();

        // Test that we can view user information if not staff or admin.
        $response = $this->asUser($viewer, ['user', 'role:staff'])->get(
            'v2/mobile/' . $user->mobile,
        );

        $response->assertStatus(401);

        $this->assertEquals(
            'The resource owner or authorization server denied the request.',
            $response->decodeResponseJson('message'),
        );
    }

    /**
     * Test that retrieving a user by mobile as staff returns full profile.
     *
     * @return void
     */
    public function testV2GetAllDataFromUserByMobileAsStaff()
    {
        $user = factory(User::class)->create([
            'mobile' => $this->faker->phoneNumber,
        ]);

        $admin = factory(User::class)->states('staff')->create();

        $response = $this->asUser($admin, ['role:staff'])->get(
            'v2/mobile/' . $user->mobile,
        );

        $response->assertStatus(200);

        // Check that fields are visible
        $response->assertJsonStructure([
            'data' => ['id', 'email', 'first_name', 'last_name', 'facebook_id'],
        ]);
    }

    /**
     * Test that retrieving a user by mobile as an admin returns full profile.
     *
     * @return void
     */
    public function testV2GetAllDataFromUserMobileAsAdmin()
    {
        $user = factory(User::class)->create([
            'mobile' => $this->faker->phoneNumber,
        ]);

        $admin = factory(User::class)->states('admin')->create();

        $response = $this->asUser($admin, ['user', 'role:admin'])->get(
            'v2/mobile/' . $user->mobile,
        );

        $response->assertStatus(200);

        // Check that fields are visible
        $response->assertJsonStructure([
            'data' => ['id', 'email', 'first_name', 'last_name', 'facebook_id'],
        ]);
    }

    /**
     * Test that retrieving a user by email as a non-admin returns a 401 response.
     *
     * @return void
     */
    public function testV2GetDataFromUserByEmailAsAnonUser()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);

        $viewer = factory(User::class)->create();

        // Test that we cannot view public profile as another user.
        $response = $this->asUser($viewer, ['user', 'user:admin'])->get(
            'v2/email/' . $user->email,
        );

        $response->assertStatus(401);

        $this->assertEquals(
            'The resource owner or authorization server denied the request.',
            $response->decodeResponseJson('message'),
        );
    }

    /**
     * Test that retrieving a user by email as staff returns full profile.
     *
     * @return void
     */
    public function testV2GetAllDataFromUserByEmailAsStaff()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);

        $admin = factory(User::class)->states('staff')->create();

        $response = $this->asUser($admin, ['role:staff'])->get(
            'v2/email/' . $user->email,
        );

        $response->assertStatus(200);

        // Check that public & private profile fields are visible
        $response->assertJsonStructure([
            'data' => ['id', 'email', 'first_name', 'last_name', 'facebook_id'],
        ]);
    }

    /**
     * Test that retrieving a user by email as an admin returns full profile.
     *
     * @return void
     */
    public function testV2GetAllDataFromUserEmailAsAdmin()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);

        $admin = factory(User::class)->states('admin')->create();

        $response = $this->asUser($admin, ['user', 'role:admin'])->get(
            'v2/email/' . $user->email,
        );

        $response->assertStatus(200);

        // Check that public & private profile fields are visible
        $response->assertJsonStructure([
            'data' => ['id', 'email', 'first_name', 'last_name', 'facebook_id'],
        ]);
    }

    /**
     * Test that write scope is required to delete a user.
     *
     * @return void
     */
    public function testV2RequiredWriteScopeDeleteUser()
    {
        $user = factory(User::class)->states('staff')->create();

        $userToDelete = factory(User::class)->create();

        $response = $this->asUser($user, ['user', 'role:staff'])->json(
            'DELETE',
            'v2/users/' . $userToDelete->id,
            [
                'first_name' => 'Hercules',
                'last_name' => 'Mulligan',
                'email' => $this->faker->email,
                'country' => 'us',
            ],
        );

        $response->assertStatus(401);

        $this->assertEquals(
            'Requires the `write` scope.',
            $response->decodeResponseJson()['hint'],
        );
    }

    /**
     * Test that admin can delete a user.
     *
     * @return void
     */
    public function testCanDeleteUser()
    {
        $user = factory(User::class)->create();

        $signups = factory(Signup::class, 4)->create([
            'northstar_id' => $user->id,
        ]);

        $posts = factory(Post::class, 10)->create([
            'signup_id' => $signups->random()->id,
            'northstar_id' => $user->id,
        ]);

        $response = $this->asAdminUser()->deleteJson("v2/users/$user->id");

        $response->assertOk();

        $this->customerIoMock->shouldHaveReceived('suppressCustomer')->once();
        $this->gambitMock->shouldHaveReceived('deleteUser');

        $this->assertUserAnonymized($user);

        // The user's posts & signups should be soft deleted, and fields that may
        // contain personally-identifiable information should be erased:
        $this->assertAnonymized($posts, ['text', 'url', 'details']);
        $this->assertAnonymized($signups, ['why_participated', 'details']);
    }

    /**
     * Test that an admin cannot add duplicates to email_subscription_topics.
     *
     * @return void
     */
    public function testV2UpdateEmailSubscriptionTopicsWithNoDupesAsAdmin()
    {
        $user = factory(User::class)->create();

        $response = $this->asAdminUser()->json('PUT', 'v2/users/' . $user->id, [
            'email_subscription_topics' => ['news', 'news'],
        ]);

        $response->assertStatus(200);

        // The email_subscription_topics should be updated with no duplicates
        $this->assertMongoDatabaseHas('users', [
            '_id' => $user->id,
            'email_subscription_topics' => ['news'],
        ]);
    }

    /**
     * Test that an admin cannot add duplicates to sms_subscription_topics.
     *
     * @return void
     */
    public function testV2UpdateSmsSubscriptionTopicsWithNoDupesAsAdmin()
    {
        $user = factory(User::class)->create();

        $response = $this->asAdminUser()->json('PUT', 'v2/users/' . $user->id, [
            'sms_subscription_topics' => ['voting', 'voting'],
        ]);

        $response->assertStatus(200);

        // The email_subscription_topics should be updated with no duplicates
        $this->assertMongoDatabaseHas('users', [
            '_id' => $user->id,
            'sms_subscription_topics' => ['voting'],
        ]);
    }

    /**
     * Test that email subscription status is true after adding topics to user with null status.
     *
     * @return void
     */
    public function testNullEmailSubscriptionStatusChangesWhenAddingTopics()
    {
        $nullStatusUser = factory(User::class)->create();

        $this->asUser($nullStatusUser, ['user', 'write'])->json(
            'PUT',
            'v2/users/' . $nullStatusUser->id,
            [
                'email_subscription_topics' => ['news'],
            ],
        );

        $this->assertMongoDatabaseHas('users', [
            '_id' => $nullStatusUser->id,
            'email_subscription_status' => true,
        ]);
    }

    /**
     * Test that email subscription status is true after adding topics to user with false status.
     *
     * @return void
     */
    public function testFalseEmailSubscriptionStatusChangesWhenAddingTopics()
    {
        $unsubscribedUser = factory(User::class)
            ->states('email-unsubscribed')
            ->create();

        $this->asUser($unsubscribedUser, ['user', 'write'])->json(
            'PUT',
            'v2/users/' . $unsubscribedUser->id,
            [
                'email_subscription_topics' => ['news'],
            ],
        );

        $this->assertMongoDatabaseHas('users', [
            '_id' => $unsubscribedUser->id,
            'email_subscription_status' => true,
        ]);
    }

    /**
     * Test that email subscription status remains true after unsetting topics.
     *
     * @return void
     */
    public function testEmailSubscriptionStatusRemainsTrueWhenClearingTopics()
    {
        $subscribedUser = factory(User::class)
            ->states('email-subscribed')
            ->create();

        $this->asUser($subscribedUser, ['user', 'write'])->json(
            'PUT',
            'v2/users/' . $subscribedUser->id,
            [
                'email_subscription_topics' => null,
            ],
        );

        $this->assertMongoDatabaseHas('users', [
            '_id' => $subscribedUser->id,
            'email_subscription_status' => true,
        ]);
    }

    /**
     * Test that user email subscription topics are cleared after setting email subscription status to false.
     *
     * @return void
     */
    public function testEmailSubscriptionTopicsAreClearedWhenUnsubscribing()
    {
        $subscribedUser = factory(User::class)
            ->states('email-subscribed')
            ->create();

        $this->asUser($subscribedUser, ['user', 'write'])->json(
            'PUT',
            'v2/users/' . $subscribedUser->id,
            [
                'email_subscription_status' => false,
            ],
        );

        $this->assertMongoDatabaseHas('users', [
            '_id' => $subscribedUser->id,
            'email_subscription_status' => false,
            'email_subscription_topics' => null,
        ]);
    }

    /**
     * Test that user SMS subscription topics are cleared after setting SMS status to stop.
     *
     * @return void
     */
    public function testSmsSubscriptionTopicsAreClearedWhenUnsubscribing()
    {
        $subscribedUser = factory(User::class)
            ->states('sms-subscribed')
            ->create();

        $this->asUser($subscribedUser, ['user', 'write'])->json(
            'PUT',
            'v2/users/' . $subscribedUser->id,
            [
                'sms_status' => 'stop',
            ],
        );

        $this->assertMongoDatabaseHas('users', [
            '_id' => $subscribedUser->id,
            'sms_status' => 'stop',
            'sms_subscription_topics' => null,
        ]);
    }

    /**
     * Test that news subscription badge is added is true after adding news as a topic on a new user.
     *
     * @return void
     */
    public function testNewsletterBadgeWhenNewUserAddsNewsSubscription()
    {
        $newsUser = factory(User::class)
            ->states('email-subscribed-news')
            ->create();

        $this->assertMongoDatabaseHas('users', [
            '_id' => $newsUser->id,
            'email_subscription_status' => true,
            'badges' => ['news-subscription'],
        ]);
    }

    /**
     * Test that a user earns the news subscription badge if they update subscription topics with news.
     *
     * @return void
     */
    public function testV2UpdateProfileEarnNewsSubscriptionBadge()
    {
        $user = factory(User::class)
            ->states('email-unsubscribed')
            ->create();

        $response = $this->asUser($user, ['user', 'write'])->json(
            'PUT',
            'v2/users/' . $user->id,
            [
                'email_subscription_topics' => ['news'],
            ],
        );

        $response->assertStatus(200);

        // The user should be updated.
        $this->assertMongoDatabaseHas('users', [
            'email_subscription_status' => true,
            'email_subscription_topics' => ['news'],
            'badges' => ['news-subscription'],
        ]);
    }

    /**
     * Test that the correct badges are added to a user even when based on historical action.
     *
     * @return void
     */
    public function testBadgesBackfillForActiveUsers()
    {
        $activeUser = factory(User::class)
            ->states('email-subscribed-news')
            ->create();
        $campaignId = $this->faker->randomNumber(4);

        $response = $this->asUser($activeUser)->postJson('api/v3/signups', [
            'campaign_id' => $campaignId,
            'details' => 'badge-testing',
        ]);

        // Make sure we get the 201 Created response
        $response->assertStatus(201);

        $this->assertMongoDatabaseHas('users', [
            '_id' => $activeUser->id,
            'badges' => ['news-subscription', 'signup'],
        ]);

        $secondResponse = $this->asAdminUser()->json(
            'PUT',
            'v2/users/' . $activeUser->id,
            [
                'badges' => [],
            ],
        );
        // simulating a "updated user"
        $activeUser->touch();

        $secondResponse->assertStatus(200);

        //check that the badges have been re added
        $this->assertMongoDatabaseHas('users', [
            '_id' => $activeUser->id,
            'badges' => ['signup', 'news-subscription'],
        ]);
    }
}
