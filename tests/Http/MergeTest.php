<?php

namespace Tests\Http;

use App\Models\User;
use Tests\BrowserKitTestCase;

class MergeTest extends BrowserKitTestCase
{
    /**
     * Test that anonymous and normal users can't merge accounts.
     *
     * @test
     */
    public function testResetNotAccessibleByNonAdmin()
    {
        $user = factory(User::class)->create();

        $this->post('v1/users/' . $user->id . '/merge');
        $this->assertResponseStatus(403);

        $this->asNormalUser()->post('v1/users/' . $user->id . '/merge');
        $this->assertResponseStatus(401);
    }

    /**
     * Test merging some accounts.
     *
     * @test
     */
    public function testMergingAccounts()
    {
        $user = User::forceCreate([
            'email' => 'target-account@example.com',
            'first_name' => 'Phil',
            'last_name' => 'Dunfy',
            'addr_street1' => '19 W 21st St',
            'addr_city' => 'New York',
            'addr_state' => 'NY',
            'addr_zip' => '10010',
            'country' => 'USA',
            'drupal_id' => '1234567',
            'source' => 'phoenix',
        ]);

        $duplicate = User::forceCreate([
            'mobile' => '5551234567',
            'mobilecommons_id' => '199483623',
            'sms_status' => 'active',
            'drupal_id' => '7175144',
            'source' => 'sms',
        ]);

        $this->asAdminUser()->json('POST', 'v1/users/' . $user->id . '/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->seeInMongoDatabase('users', [
            '_id' => $user->id,
            'email' => $user->email,
            'mobile' => $duplicate->mobile,
            'mobilecommons_id' => $duplicate->mobilecommons_id,
            'sms_status' => $duplicate->sms_status,
            'drupal_id' => '1234567',
            'sms_subscription_topics' => ['general', 'voting'],
        ]);

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInMongoDatabase('users', [
            '_id' => $duplicate->id,
            'email' => 'merged-account-' . $user->id . '@dosomething.invalid',
            'mobile' => null,
            'mobilecommons_id' => null,
            'sms_status' => null,
            'drupal_id' => '7175144',
        ]);
    }

    /**
     * Test last_authenticated_at merge logic.
     *
     * @test
     */
    public function testMergingLastAuthenticatedAt()
    {
        $user = User::forceCreate([
            'email' => 'target-account@example.com',
            'last_authenticated_at' => '2018-02-28 00:00:00',
        ]);

        $duplicate = User::forceCreate([
            'mobile' => '5551234567',
            'last_authenticated_at' => '2018-02-28 01:00:00',
        ]);

        $this->asAdminUser()->json('POST', 'v1/users/' . $user->id . '/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals(
            $user->fresh()->last_authenticated_at->toTimeString(),
            '01:00:00',
        );

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInMongoDatabase('users', [
            '_id' => $duplicate->id,
            'last_authenticated_at' => null,
        ]);
    }

    /**
     * Test last_messaged_at merge logic.
     *
     * @test
     */
    public function testMergingLastMessagedAt()
    {
        $user = User::forceCreate([
            'email' => 'target-account@example.com',
            'last_messaged_at' => '2018-02-28 00:00:00',
        ]);

        $duplicate = User::forceCreate([
            'mobile' => '5551234567',
            'last_messaged_at' => '2018-02-28 01:00:00',
        ]);

        $this->asAdminUser()->json('POST', 'v1/users/' . $user->id . '/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals(
            $user->fresh()->last_messaged_at->toTimeString(),
            '01:00:00',
        );

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInMongoDatabase('users', [
            '_id' => $duplicate->id,
            'last_authenticated_at' => null,
        ]);
    }

    /**
     * Test last_accessed_at merge logic.
     *
     * @test
     */
    public function testMergingLastaccessedAt()
    {
        $user = User::forceCreate([
            'email' => 'target-account@example.com',
            'last_accessed_at' => '2018-02-28 00:00:00',
        ]);

        $duplicate = User::forceCreate([
            'mobile' => '5551234567',
            'last_accessed_at' => '2018-02-28 01:00:00',
        ]);

        $this->asAdminUser()->json('POST', 'v1/users/' . $user->id . '/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals(
            $user->fresh()->last_accessed_at->toTimeString(),
            '01:00:00',
        );

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInMongoDatabase('users', [
            '_id' => $duplicate->id,
            'last_accessed_at' => null,
        ]);
    }

    /**
     * Test language merge logic.
     *
     * @test
     */
    public function testMergingLanguage()
    {
        $user = User::forceCreate([
            'email' => 'target-account@example.com',
            'last_accessed_at' => '2018-02-28 00:00:00',
            'language' => 'hi',
        ]);

        $duplicate = User::forceCreate([
            'mobile' => '5551234567',
            'last_accessed_at' => '2018-02-28 01:00:00',
            'language' => 'yo',
        ]);

        $this->asAdminUser()->json('POST', 'v1/users/' . $user->id . '/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals($user->fresh()->language, 'yo');

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInMongoDatabase('users', [
            '_id' => $duplicate->id,
            'language' => null,
            'last_accessed_at' => null,
        ]);
    }

    /**
     * Test first_name merge logic.
     *
     * @test
     */
    public function testMergingFirstName()
    {
        $user = User::forceCreate([
            'email' => 'target-account@example.com',
            'first_name' => 'Not Me',
        ]);

        $this->mockTime('+1 minute');

        $duplicate = User::forceCreate([
            'mobile' => '5551234567',
            'first_name' => 'Keep Me',
        ]);

        $this->asAdminUser()->json('POST', 'v1/users/' . $user->id . '/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals($user->fresh()->first_name, 'Keep Me');

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInMongoDatabase('users', [
            '_id' => $duplicate->id,
            'first_name' => null,
        ]);
    }

    /**
     * Test last_name merge logic.
     *
     * @test
     */
    public function testMergingLastName()
    {
        $user = User::forceCreate([
            'email' => 'target-account@example.com',
            'last_name' => 'Older',
        ]);

        $this->mockTime('+1 minute');

        $duplicate = User::forceCreate([
            'mobile' => '5551234567',
            'last_name' => 'Newer',
        ]);

        $this->asAdminUser()->json('POST', 'v1/users/' . $user->id . '/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals($user->fresh()->last_name, 'Newer');

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInMongoDatabase('users', [
            '_id' => $duplicate->id,
            'first_name' => null,
        ]);
    }

    /**
     * Test birthdate merge logic.
     *
     * @test
     */
    public function testMergingBirthdate()
    {
        $user = User::forceCreate([
            'email' => 'target-account@example.com',
            'birthdate' => '1995-03-15',
        ]);

        $this->mockTime('+1 minute');

        $duplicate = User::forceCreate([
            'mobile' => '5551234567',
            'birthdate' => '1991-04-20',
        ]);

        $this->asAdminUser()->json('POST', 'v1/users/' . $user->id . '/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals(
            format_date($user->fresh()->birthdate, 'Y-m-d'),
            '1991-04-20',
        );

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInMongoDatabase('users', [
            '_id' => $duplicate->id,
            'first_name' => null,
        ]);
    }

    /**
     * Test that you can't merge without the write scope.
     *
     * @test
     */
    public function testMergingWithoutWriteScope()
    {
        $admin = factory(User::class)->states('admin')->create();

        $user = User::forceCreate([
            'email' => 'target-account@example.com',
            'last_accessed_at' => '2018-02-28 00:00:00',
            'language' => 'hi',
        ]);

        $duplicate = User::forceCreate([
            'mobile' => '5551234567',
            'last_accessed_at' => '2018-02-28 01:00:00',
            'language' => 'yo',
        ]);

        $this->asUser($admin, ['role:admin', 'user'])->json(
            'POST',
            'v1/users/' . $user->id . '/merge',
            [
                'id' => $duplicate->id,
            ],
        );

        $this->assertResponseStatus(401);
        $this->assertEquals(
            'Requires the `write` scope.',
            $this->response->decodeResponseJson()['hint'],
        );
    }
}
