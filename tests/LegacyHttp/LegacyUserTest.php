<?php

namespace Tests\LegacyHttp;

use App\Models\User;
use App\Services\CustomerIo;
use App\Services\Gambit;
use Tests\BrowserKitTestCase;

class LegacyUserTest extends BrowserKitTestCase
{
    /**
     * Test for retrieving a user by their ID.
     *
     * @return void
     */
    public function testGetUserById()
    {
        $user = User::create([
            'email' => 'jbeaubier@xavier.edu',
            'first_name' => 'Jean-Paul',
        ]);

        $this->withLegacyApiKeyScopes(['user'])->get(
            'v1/users/id/' . $user->id,
        );
        $this->assertResponseStatus(200);
        $this->seeJsonField('data.id', $user->id);
    }

    /**
     * Test for retrieving a user by their Mongo _id, for backwards compatibility.
     *
     * @return void
     */
    public function testGetUserByMongoId()
    {
        $user = User::create([
            'email' => 'jbeaubier@xavier.edu',
            'first_name' => 'Jean-Paul',
        ]);

        $this->withLegacyApiKeyScopes(['user'])->get(
            'v1/users/_id/' . $user->id,
        );
        $this->assertResponseStatus(200);
        $this->seeJsonField('data.id', $user->id);
    }

    /**
     * Test for retrieving a user by their email.
     *
     * @return void
     */
    public function testGetUserByEmail()
    {
        $user = User::create([
            'email' => 'jbeaubier@xavier.edu',
            'first_name' => 'Jean-Paul',
        ]);

        $this->withLegacyApiKeyScopes(['user', 'admin'])->get(
            'v1/users/email/JBeaubier@Xavier.edu',
        );
        $this->assertResponseStatus(200);
        $this->seeJsonField('data.id', $user->id);
    }

    /**
     * Test for retrieving a user by their mobile number.
     *
     * @return void
     */
    public function testGetUserByMobile()
    {
        $user = User::create([
            'mobile' => $this->faker->phoneNumber,
            'first_name' => $this->faker->firstName,
        ]);

        $this->withLegacyApiKeyScopes(['user', 'admin'])->get(
            'v1/users/mobile/' . $user->mobile,
        );
        $this->assertResponseStatus(200);
        $this->seeJsonField('data.id', $user->id);
    }

    /**
     * Test we can't retrieve a user by a non-indexed field.
     *
     * @return void
     */
    public function testCantGetUserByNonIndexedField()
    {
        User::create([
            'mobile' => $this->faker->phoneNumber,
            'first_name' => 'Bobby',
        ]);

        // Test that we return 404 when retrieving by a non-indexed field.
        $this->withLegacyApiKeyScopes(['user'])->get(
            'v1/users/first_name/Bobby',
        );
        $this->assertResponseStatus(404);
    }

    /**
     * Tests retrieving a user by their Drupal ID.
     */
    public function testRetrieveUser()
    {
        $user = factory(User::class)->create(['drupal_id' => '100010']);

        // GET /users/drupal_id/<drupal_id>
        $this->withLegacyApiKeyScopes(['user'])->get(
            'v1/users/drupal_id/100010',
        );
        $this->assertResponseStatus(200);
        $this->seeJsonField('data.id', $user->id);
        $this->seeJsonField('data.drupal_id', $user->drupal_id);
    }

    /**
     * Test for retrieving a user with an admin key.
     *
     * @return void
     */
    public function testGetAllDataFromUser()
    {
        $user = User::create([
            'email' => $this->faker->unique()->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
        ]);

        $this->withLegacyApiKeyScopes(['user', 'admin'])->get(
            'v1/users/_id/' . $user->id,
        );
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id',
                'email',
                'first_name',
                'last_name',
                'sms_subscription_topics',
            ],
        ]);
    }

    /**
     * Test retrieving multiple users.
     *
     * @return void
     */
    public function testIndex()
    {
        // Make some test users to see in the index.
        factory(User::class, 5)->create();

        $this->withLegacyApiKeyScopes(['user'])->get('v1/users');
        $this->assertResponseStatus(403);

        $this->withLegacyApiKeyScopes(['admin', 'user'])->get('v1/users');
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
            'meta' => [
                'pagination' => [
                    'total',
                    'count',
                    'per_page',
                    'current_page',
                    'links',
                ],
            ],
        ]);
    }

    /**
     * Test retrieving multiple users.
     *
     * @return void
     */
    public function testIndexPagination()
    {
        // Make some test users to see in the index.
        factory(User::class, 5)->create();

        $this->withLegacyApiKeyScopes(['admin', 'user'])->get(
            'v1/users?limit=200',
        ); // set a "per page" above the allowed max
        $this->assertResponseStatus(200);
        $this->assertSame(
            100,
            $this->response->json('meta.pagination.per_page'),
        );

        $this->seeJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
            'meta' => [
                'pagination' => [
                    'total',
                    'count',
                    'per_page',
                    'current_page',
                    'links',
                ],
            ],
        ]);
    }

    /**
     * Test for retrieving a nonexistent User.
     *
     * @return void
     */
    public function testNonexistentUser()
    {
        $this->withLegacyApiKeyScopes(['user'])->get('v1/users/_id/FAKE');
        $this->assertResponseStatus(404);
    }

    /**
     * Tests retrieving multiple users by their id.
     */
    public function testFilterUsersById()
    {
        $user1 = factory(User::class)->create([
            'email' => $this->faker->unique()->email,
            'drupal_id' => '123411',
        ]);

        $user2 = factory(User::class)->create([
            'email' => $this->faker->unique()->email,
            'drupal_id' => '123412',
        ]);

        $user3 = factory(User::class)->create([
            'mobile' => $this->faker->unique()->phoneNumber,
            'drupal_id' => '123413',
        ]);

        // Retrieve multiple users by _id
        $this->withLegacyApiKeyScopes(['admin', 'user'])->get(
            'v1/users?filter[id]=' . $user1->id . ',' . $user2->id . ',FAKE_ID',
        );
        $this->assertCount(2, $this->response->json('data'));
        $this->seeJsonStructure([
            'data' => [
                '*' => ['id'],
            ],
            'meta' => ['pagination'],
        ]);

        // Retrieve multiple users by drupal_id
        $this->withLegacyApiKeyScopes(['admin', 'user'])->get(
            'v1/users?filter[drupal_id]=FAKE_ID,' .
                $user1->drupal_id .
                ',' .
                $user2->drupal_id .
                ',' .
                $user3->drupal_id,
        );
        $this->assertCount(3, $this->response->json('data'));

        // Test compound queries
        $this->withLegacyApiKeyScopes(['admin', 'user'])->get(
            'v1/users?filter[drupal_id]=FAKE_ID,' .
                $user1->drupal_id .
                ',' .
                $user2->drupal_id .
                ',' .
                $user3->drupal_id .
                '&filter[_id]=' .
                $user1->id,
        );
        $this->assertCount(1, $this->response->json('data'));
    }

    /**
     * Tests searching users.
     */
    public function testSearchUsers()
    {
        // Make a target user to search for & some others.
        factory(User::class)->create([
            'email' => 'search-result@dosomething.org',
        ]);

        factory(User::class, 2)->create(['mobile' => null]);

        // Search should be limited to `admin` scoped keys.
        $this->withLegacyApiKeyScopes(['user'])->get(
            'v1/users?search[email]=search-result@dosomething.org',
        );
        $this->assertResponseStatus(403);

        // Query by a "known" search term.
        $query = 'search-result@dosomething.org';
        $this->withLegacyApiKeyScopes(['admin', 'user'])->get(
            'v1/users?search[id]=' .
                $query .
                '&search[email]=' .
                $query .
                '&search[mobile]=' .
                $query,
        );
        $this->assertResponseStatus(200);

        // There should be one match (a user with the provided email)
        $this->assertCount(1, $this->response->json('data'));
    }

    /**
     * Test that creating multiple users won't trigger unique
     * database constraint errors.
     *
     * @return void
     */
    public function testCreateMultipleUsers()
    {
        // Create some new users
        for ($i = 0; $i < 5; $i++) {
            $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
                'POST',
                'v1/users',
                [
                    'email' => $this->faker->unique()->email,
                    'mobile' => '', // this should not save a `mobile` field on these users
                    'source' => 'phpunit',
                ],
            );

            $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
                'POST',
                'v1/users',
                [
                    'email' => '  ', // this should not save a `email` field on these users
                    'mobile' => $this->faker->unique()->phoneNumber,
                    'source' => 'phpunit',
                ],
            );
        }

        $this->get('v1/users');
        $this->assertCount(10, $this->response->json('data'));
    }

    /**
     * Test that creating a user requires the write scope.
     *
     * @return void
     */
    public function testCreateUserRequiresWriteScope()
    {
        $this->withLegacyApiKeyScopes(['admin', 'user'])->json(
            'POST',
            'v1/users',
            [
                'email' => $this->faker->unique()->email,
                'mobile' => '', // this should not save a `mobile` field on these users
                'source' => 'phpunit',
            ],
        );

        $this->assertResponseStatus(403);
        $this->assertEquals(
            'You must be using an API key with "write" scope to do that.',
            $this->response->json('error.message'),
        );
    }

    /**
     * Test that you set an indexed field to an empty string. This would cause
     * unique constraint violations if multiple users had an empty string set
     * for a unique indexed field.
     *
     * @return void
     */
    public function testCantMakeIndexEmptyString()
    {
        $user = User::create([
            'email' => $this->faker->email,
            'mobile' => $this->faker->phoneNumber,
            'first_name' => $this->faker->firstName,
        ]);

        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'PUT',
            'v1/users/_id/' . $user->id,
            [
                'mobile' => '', // this should remove the `mobile` field from the document
            ],
        );

        $this->seeInMongoDatabase('users', ['_id' => $user->id]);

        $document = $this->getMongoDocument('users', $user->id);
        $this->assertArrayNotHasKey('mobile', $document);
    }

    /**
     * Test that you can't remove the only index (email or mobile) from a field.
     *
     * @return void
     */
    public function testCantRemoveOnlyIndex()
    {
        $user = User::create([
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
        ]);

        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'PUT',
            'v1/users/_id/' . $user->id,
            [
                'email' => '',
            ],
        );

        $this->assertResponseStatus(422);
    }

    /**
     * Test that you can't remove *both* the email and mobile fields from a user.
     *
     * @return void
     */
    public function testCantRemoveBothEmailAndMobile()
    {
        $user = User::create([
            'email' => $this->faker->email,
            'mobile' => $this->faker->phoneNumber,
            'first_name' => $this->faker->firstName,
        ]);

        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'PUT',
            'v1/users/_id/' . $user->id,
            [
                'email' => '',
                'mobile' => '',
            ],
        );
        $this->assertResponseStatus(422);
    }

    /**
     * Test that you can't edit a user without write scope.
     *
     * @return void
     */
    public function testCantEditWithoutWriteScope()
    {
        $user = User::create([
            'email' => $this->faker->email,
            'mobile' => $this->faker->phoneNumber,
            'first_name' => $this->faker->firstName,
        ]);

        $this->withLegacyApiKeyScopes(['admin', 'user'])->json(
            'PUT',
            'v1/users/_id/' . $user->id,
            [
                'email' => '',
            ],
        );

        $this->assertResponseStatus(403);
        $this->assertEquals(
            'You must be using an API key with "write" scope to do that.',
            $this->response->json('error.message'),
        );
    }

    /**
     * Test that we can't create a duplicate user.
     *
     * @return void
     */
    public function testCreateDuplicateUser()
    {
        User::create(['mobile' => '1235557878']);

        User::create(['email' => 'existing-person@example.com']);

        // Create a new user object
        $payload = [
            'email' => 'Existing-Person@example.com',
            'mobile' => '(123) 555-7878',
            'source' => 'phpunit',
        ];

        // This should cause a validation error.
        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'POST',
            'v1/users',
            $payload,
        );
        $this->assertResponseStatus(422);
    }

    /**
     * Test that we can't create a duplicate user by saving a user
     * with a different capitalization in their email.
     */
    public function testCantCreateDuplicateUserByIndexCapitalization()
    {
        $user = User::create([
            'email' => 'existing-user@dosomething.org',
        ]);

        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'POST',
            'v1/users',
            [
                'email' => 'EXISTING-USER@dosomething.org',
                'source' => 'phpunit',
            ],
        );

        $this->assertResponseStatus(200);
        $this->assertSame($this->response->json('data.id'), $user->_id);
    }

    /**
     * Test that we can upsert based on a Drupal ID.
     *
     * @return void
     */
    public function testCanUpsertByDrupalId()
    {
        $user = factory(User::class)->create([
            'email' => 'existing-person@example.com',
            'drupal_id' => '123123',
        ]);

        // Try to make a conflict up by upserting something that would match 2 accounts.
        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'POST',
            'v1/users',
            [
                'drupal_id' => '123123',
                'first_name' => 'Bob',
            ],
        );

        $this->assertResponseStatus(200);

        $user = $user->fresh();

        $this->assertEquals('Bob', $user->first_name);
    }

    /**
     * Test that we can't create a duplicate user.
     *
     * @return void
     */
    public function testCantCreateDuplicateDrupalUser()
    {
        factory(User::class)->create([
            'email' => 'existing-person@example.com',
            'drupal_id' => '123123',
        ]);

        factory(User::class)->create([
            'email' => 'other-existing-user@example.com',
        ]);

        // Try to make a conflict up by upserting something that would match 2 accounts.
        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'POST',
            'v1/users',
            [
                'email' => 'other-existing-user@example.com',
                'drupal_id' => '123123',
            ],
        );

        $this->assertResponseStatus(422);
    }

    /**
     * Test that we can't create a duplicate user by "upserting" an existing
     * user and adding a new index in that operation.
     */
    public function testCanUpsertWithAnAdditionalIndex()
    {
        $user = User::create([
            'mobile' => '2035551238',
        ]);

        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'POST',
            'v1/users',
            [
                'email' => 'lalalala@dosomething.org',
                'mobile' => '2035551238',
                'source' => 'phpunit',
            ],
        );

        $this->assertResponseStatus(200);
        $this->assertSame($this->response->json('data.id'), $user->_id);
    }

    /**
     * Test for "upserting" an existing user.
     *
     * @return void
     */
    public function testUpsertUser()
    {
        factory(User::class)->create([
            'email' => 'upsert-me@dosomething.org',
            'mobile' => null, // <-- overriding factory so we can add it via upsert
            'source' => 'database',
        ]);

        // Post a "new" user object to merge into existing record
        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'POST',
            'v1/users',
            [
                'email' => 'upsert-me@dosomething.org',
                'mobile' => '5556667777',
                'password' => 'secret',
                'first_name' => 'Puppet',
                'source' => 'phpunit',
                'role' => 'admin',
            ],
        );

        // The response should return JSON with a 200 Okay status code
        $this->assertResponseStatus(200);

        $this->seeJsonField('data.email', 'upsert-me@dosomething.org');

        // Check for the new fields we "upserted":
        $this->seeJsonField('data.first_name', 'Puppet');
        $this->seeJsonField('data.mobile', '5556667777');

        // Ensure the `source` field is immutable (since we did not provide an earlier creation date):
        $this->seeJsonField('data.source', 'database');

        // The role should *not* be changed by upsert (since that'd make it easily to accidentally grant!)
        $this->seeJsonField('data.role', 'user');
    }

    /**
     * Test for opting out of upsert functionality via a query string.
     *
     * @return void
     */
    public function testOptOutOfUpsertingUser()
    {
        $user = User::create([
            'email' => 'do-not-upsert-me@dosomething.org',
            'source' => 'database',
        ]);

        // Post a "new" user object to merge into existing record
        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'POST',
            'v1/users?upsert=false',
            [
                'email' => $user->email,
                'first_name' => 'Puppet',
            ],
        );

        // The response should return 422 Unprocessable Entity with the existing item.
        $this->assertResponseStatus(422);
        $this->assertEquals(
            $user->id,
            $this->response->json('error.context.id'),
        );
    }

    /**
     * Test that we can still create a new user when we've opted out of upserting.
     *
     * @return void
     */
    public function testCreateUserWhileOptingOutOfUpsert()
    {
        // Post a "new" user object to merge into existing record
        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'POST',
            'v1/users?upsert=false',
            [
                'email' => $this->faker->email,
                'first_name' => 'Puppet',
            ],
        );

        // This should still be allowed, since the account doesn't exist.
        $this->assertResponseStatus(201);
    }

    /**
     * Test that "upserting" an existing user can't change an existing
     * user's account if *all* given credentials don't match.
     *
     * @return void
     */
    public function testCantUpsertUserWithoutAllMatchingCredentials()
    {
        $user = User::create([
            'email' => 'upsert-me@dosomething.org',
            'mobile' => '5556667777',
        ]);

        // Post a "new" user object to merge into existing record
        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'POST',
            'v1/users',
            [
                'email' => 'upsert-me+2@dosomething.org',
                'mobile' => '5556667777',
                'first_name' => 'Puppet',
            ],
        );

        // The existing record should be unchanged.
        $this->seeInMongoDatabase('users', [
            '_id' => $user->id,
            'email' => 'upsert-me@dosomething.org',
            'mobile' => '+15556667777',
        ]);

        // The response should indicate a validation conflict!
        $this->assertResponseStatus(422);
        $this->seeJsonField('error.code', 422);
        $this->seeJsonField('error.message', 'Failed validation.');
        $this->seeJsonField('error.fields', [
            'email' => ['Cannot upsert an existing index.'],
        ]);
    }

    /**
     * Test for updating an existing user.
     *
     * @return void
     */
    public function testUpdateUser()
    {
        $user = User::create(['mobile' => '+15543694724']);

        // Update an existing user
        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'PUT',
            'v1/users/_id/' . $user->id,
            [
                'email' => 'NewEmail@dosomething.org',
            ],
        );

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email', 'newemail@dosomething.org');
        $this->seeJsonField('data.mobile', '5543694724'); // unchanged user values should remain unchanged

        // Verify user data got updated
        $this->seeInMongoDatabase('users', [
            '_id' => $user->id,
            'mobile' => $user->mobile,
            'email' => 'newemail@dosomething.org',
        ]);
    }

    /**
     * Test for updating an existing user's index.
     *
     * @return void
     */
    public function testUpdateUserIndex()
    {
        $user = User::create(['email' => 'email@dosomething.org']);

        // Update an existing user
        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'PUT',
            'v1/users/_id/' . $user->id,
            [
                'email' => 'new-email@dosomething.org',
            ],
        );

        $this->assertResponseStatus(200);

        // Verify user data got updated
        $this->seeInMongoDatabase('users', [
            '_id' => $user->id,
            'email' => 'new-email@dosomething.org',
        ]);
    }

    /**
     * Test that we can't update a user's profile to have duplicate
     * identifiers with someone else.
     */
    public function testUpdateWithConflict()
    {
        User::create(['mobile' => '5555550101']);

        $user = User::create(['email' => 'admiral.ackbar@example.com']);

        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'PUT',
            'v1/users/_id/' . $user->id,
            [
                'mobile' => '(555) 555-0101', // the existing user account
                'first_name' => 'Gial',
                'last_name' => 'Ackbar',
            ],
        );

        $this->assertResponseStatus(422);
    }

    /**
     * Test that we can't update a user's profile to have duplicate
     * identifiers with someone else.
     */
    public function testUpdateWithDrupalIDConflict()
    {
        $user1 = factory(User::class)->create(['drupal_id' => '123456']);

        $user2 = factory(User::class)->create(['drupal_id' => '555123']);

        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->json(
            'PUT',
            'v1/users/_id/' . $user2->id,
            [
                'drupal_id' => '123456', // the existing user account
            ],
        );

        // The `drupal_id` field is ignored as un-fillable.
        $this->assertResponseStatus(200);
        $this->assertEquals('123456', $user1->fresh()->drupal_id);
        $this->assertEquals('555123', $user2->fresh()->drupal_id);
    }

    /**
     * Test for deleting an existing user.
     *
     * @return void
     */
    public function testDelete()
    {
        $user = User::create(['email' => 'delete-me@example.com']);

        $this->mock(Gambit::class)
            ->shouldReceive('deleteUser')
            ->once();
        $this->mock(CustomerIo::class)
            ->shouldReceive('suppressCustomer')
            ->once();

        // Only 'admin' scoped keys should be able to delete users.
        $this->withLegacyApiKeyScopes(['user', 'write'])->delete(
            'v1/users/' . $user->id,
        );
        $this->assertResponseStatus(403);

        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->delete(
            'v1/users/' . $user->id,
        );
        $this->assertResponseStatus(200);
    }

    /**
     * Test the write scope is required to delete an existing user.
     *
     * @return void
     */
    public function testDeleteWithoutWriteScope()
    {
        $user = User::create(['email' => 'delete-me@example.com']);

        $this->withLegacyApiKeyScopes(['admin', 'user'])->delete(
            'v1/users/' . $user->id,
        );

        $this->assertResponseStatus(403);
        $this->assertEquals(
            'You must be using an API key with "write" scope to do that.',
            $this->response->json('error.message'),
        );
    }

    /**
     * Test for deleting a user that does not exist.
     *
     * @return void
     */
    public function testDeleteNoResource()
    {
        $this->withLegacyApiKeyScopes(['admin', 'user', 'write'])->delete(
            'v1/users/DUMMY_ID',
        );
        $this->assertResponseStatus(404);
    }

    /**
     * Test retrieving multiple users.
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
     *
     * @return void
     */
    public function testIndexVisibleToStaffRole()
    {
        // Make a staff user & some test users.
        $staff = factory(User::class)
            ->states('staff')
            ->create();

        factory(User::class, 5)->create();

        $this->asUser($staff, ['role:staff', 'user'])->get('v1/users');
        $this->assertResponseStatus(200);
    }

    /**
     * Test retrieving multiple users.
     *
     * @return void
     */
    public function testIndexVisibleToAdminRole()
    {
        // Make a admin & some test users.
        $admin = factory(User::class)
            ->states('admin')
            ->create();

        factory(User::class, 5)->create();

        $this->asUser($admin, ['role:admin', 'user'])->get('v1/users');
        $this->assertResponseStatus(200);
    }

    /**
     * Test that we can filter records by date range.
     *
     * @return void
     */
    public function testFilterByDateField()
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

        $this->withAccessToken(['admin', 'user'])->json(
            'GET',
            'v1/users?before[updated_at]=1/1/2010',
        );
        $this->assertCount(
            4,
            $this->response->json('data'),
            'can filter `updated_at` before timestamp',
        );

        $this->withAccessToken(['admin', 'user'])->json(
            'GET',
            'v1/users?after[updated_at]=1/1/2015',
        );
        $this->assertCount(
            6,
            $this->response->json('data'),
            'can filter `updated_at` after timestamp',
        );

        $this->withAccessToken(['admin', 'user'])->json(
            'GET',
            'v1/users?before[updated_at]=1/2/2015&after[updated_at]=12/31/2009',
        );
        $this->assertCount(
            5,
            $this->response->json('data'),
            'can filter `updated_at` between two timestamps',
        );
    }

    /**
     * Test that retrieving a user as a non-admin returns limited profile.
     *
     * @return void
     */
    public function testGetPublicDataFromUser()
    {
        $user = factory(User::class)->create();

        $viewer = factory(User::class)->create();

        // Test that we can view public profile as another user.
        $this->asUser($viewer, ['user', 'user:admin'])->get(
            'v1/users/_id/' . $user->id,
        );
        $this->assertResponseStatus(200);

        // And test that private profile fields are hidden for the other user.
        $data = $this->response->json('data');
        $this->assertArrayHasKey('first_name', $data);
        $this->assertArrayNotHasKey('last_name', $data);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('mobile', $data);
        $this->assertArrayNotHasKey('facebook_id', $data);
    }

    /**
     * Test that retrieving a user as an admin returns full profile.
     *
     * @return void
     */
    public function testGetAllDataFromUserAsStaff()
    {
        $user = factory(User::class)->create();

        $admin = factory(User::class)
            ->states('staff')
            ->create();

        $this->asUser($admin, ['user', 'user:admin'])->get(
            'v1/users/id/' . $user->id,
        );
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => ['id', 'email', 'first_name', 'last_name', 'facebook_id'],
        ]);
    }

    /**
     * Test that retrieving a user as an admin returns full profile.
     *
     * @return void
     */
    public function testGetAllDataFromUserAsAdmin()
    {
        $user = factory(User::class)->create();

        $admin = factory(User::class)
            ->states('admin')
            ->create();

        $this->asUser($admin, ['user', 'user:admin'])->get(
            'v1/users/id/' . $user->id,
        );
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => ['id', 'email', 'first_name', 'last_name', 'facebook_id'],
        ]);
    }

    /**
     * Test that a staffer can update a user's profile.
     *
     * @return void
     */
    public function testUpdateProfileAsStaff()
    {
        $user = factory(User::class)->create();

        $staff = factory(User::class)
            ->states('staff')
            ->create();

        $this->asUser($staff, ['user', 'role:staff', 'write'])->json(
            'PUT',
            'v1/users/id/' . $user->id,
            [
                'first_name' => 'Alexander',
                'last_name' => 'Hamilton',
            ],
        );

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

        $staff = factory(User::class)
            ->states('staff')
            ->create();

        $this->asUser($staff, ['user', 'role:staff', 'write'])->json(
            'PUT',
            'v1/users/id/' . $user->id,
            [
                'mobile' => '',
            ],
        );

        $this->assertResponseStatus(200);

        // The user field should have been removed.
        $this->assertNull($user->fresh()->mobile);
    }

    /** @test */
    public function testUnsetFieldWithNull()
    {
        $user = factory(User::class)->create();

        $staff = factory(User::class)
            ->states('staff')
            ->create();

        $this->asUser($staff, ['user', 'role:staff', 'write'])->json(
            'PUT',
            'v1/users/id/' . $user->id,
            [
                'mobile' => null,
            ],
        );

        $this->assertResponseStatus(200);

        // The user field should have been removed.
        $this->assertNull($user->fresh()->mobile);
    }

    /**
     * Test that a staffer cannot change a user's role.
     *
     * @return void
     */
    public function testGrantRoleAsStaff()
    {
        $user = factory(User::class)->create();

        $staff = factory(User::class)
            ->states('staff')
            ->create();

        $this->asUser($staff, ['user', 'role:staff'])->json(
            'PUT',
            'v1/users/id/' . $user->id,
            [
                'role' => 'admin',
            ],
        );

        $this->assertResponseStatus(401);
    }

    /**
     * Test that an admin can create a new user.
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
        $this->seeJsonField('data.first_name', 'Hercules');
        $this->seeJsonField('data.last_name', 'Mulligan');
        $this->seeJsonField('data.country', 'US'); // mutator should capitalize country codes!
        $this->seeJsonField('data.source', 'historical');
        $this->seeJsonField('data.source_detail', 'american-revolution');
    }

    /**
     * Test that an admin can update a user's profile, including their role.
     *
     * @return void
     */
    public function testUpdateProfileAsAdmin()
    {
        $user = factory(User::class)->create();

        $this->asAdminUser()->json('PUT', 'v1/users/id/' . $user->id, [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'role' => 'admin',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.first_name', 'Hercules');
        $this->seeJsonField('data.last_name', 'Mulligan');
        $this->seeJsonField('data.role', 'admin');
    }

    /**
     * Test that creating a user results in saving normalized data.
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
        $this->seeInMongoDatabase('users', [
            'first_name' => 'Batman',
            'email' => 'batman@example.com',
            'mobile' => '+12223335555',
        ]);
    }

    /**
     * Test that we can still set old `mobilecommons_status` field.
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
        $this->seeInMongoDatabase('users', [
            'mobile' => '+12223335555',
            'sms_status' => 'active',
        ]);
    }

    /**
     * Test that the `country` field is removed if it
     * does not contain a valid ISO-3166 country code.
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
        $this->seeJsonField('data.last_name', '└(^o^)┘');
        $this->seeJsonField('data.last_initial', '└');
    }

    /**
     * Test that ISO-8601 formatted date strings are accepted.
     *
     * @return void
     */
    public function testDateFields()
    {
        $user = factory(User::class)->create();

        $newTimestamp = '2017-11-02T18:42:00.000Z';

        $this->asAdminUser()->putJson('v1/users/id/' . $user->id, [
            'last_messaged_at' => $newTimestamp,
        ]);

        $this->assertResponseStatus(200);
        $this->assertEquals(
            '2017-11-02T18:42:00+00:00',
            $user->fresh()->last_messaged_at->toIso8601String(),
        );
    }

    /**
     * Test that we cant upsert created_at to be earlier.
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

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email', $user->email);
        $this->seeJsonField('data.first_name', 'Daisy');

        // It should ignore the new `source, `source_detail`, and `created_at`.
        $this->seeJsonField('data.source', 'television');
        $this->seeJsonField('data.source_detail', 'agents-of-shield');
        $this->seeJsonField(
            'data.created_at',
            $user->created_at->toISO8601String(),
        );
    }

    /**
     * Test that we can filter records with both ?search[email]=test@dosomething.org
     * and ?search=test@dosomething.org patterns.
     *
     * @return void
     */
    public function testFilterBySearchFieldParam()
    {
        $user = factory(User::class)->create(['email' => $this->faker->email]);

        $this->withAccessToken(['admin', 'user'])->json(
            'GET',
            'v1/users?search[email]=' . $user->email,
        );
        $this->assertCount(1, $this->response->json('data'));
        $this->assertEquals(
            $this->response->json('data.0.email'),
            $user->email,
        );

        $this->withAccessToken(['admin', 'user'])->json(
            'GET',
            'v1/users?search=' . $user->email,
        );
        $this->assertCount(1, $this->response->json('data'));
        $this->assertEquals(
            $this->response->json('data.0.email'),
            $user->email,
        );
    }
}
