<?php

use Northstar\Models\User;

class MergeTest extends BrowserKitTestCase
{
    /**
     * Test that anonymous and normal users can't merge accounts.
     * POST /users/:id/merge
     *
     * @test
     */
    public function testResetNotAccessibleByNonAdmin()
    {
        $user = factory(User::class)->create();

        $this->post('v1/users/'.$user->id.'/merge');
        $this->assertResponseStatus(403);

        $this->asNormalUser()->post('v1/users/'.$user->id.'/merge');
        $this->assertResponseStatus(401);
    }

    /**
     * Test merging some accounts.
     * POST /resets
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
            'city' => 'New York',
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

        $this->asAdminUser()->json('POST', 'v1/users/'.$user->id.'/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->seeInDatabase('users', [
            '_id' => $user->id,
            'email' => $user->email,
            'mobile' => $duplicate->mobile,
            'mobilecommons_id' => $duplicate->mobilecommons_id,
            'sms_status' => $duplicate->sms_status,
            'drupal_id' => '1234567',
        ]);

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInDatabase('users', [
            '_id' => $duplicate->id,
            'email' => 'merged-account-'.$user->id.'@dosomething.invalid',
            'mobile' => null,
            'mobilecommons_id' => null,
            'sms_status' => null,
            'drupal_id' => '7175144',
        ]);
    }

    /**
     * Test last_authenticated_at merge logic.
     * POST /resets
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

        $this->asAdminUser()->json('POST', 'v1/users/'.$user->id.'/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals($user->fresh()->last_authenticated_at->toTimeString(), '01:00:00');

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInDatabase('users', [
            '_id' => $duplicate->id,
            'last_authenticated_at' => null,
        ]);
    }

    /**
     * Test last_messaged_at merge logic.
     * POST /resets
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

        $this->asAdminUser()->json('POST', 'v1/users/'.$user->id.'/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals($user->fresh()->last_messaged_at->toTimeString(), '01:00:00');

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInDatabase('users', [
            '_id' => $duplicate->id,
            'last_authenticated_at' => null,
        ]);
    }

    /**
     * Test last_accessed_at merge logic.
     * POST /resets
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

        $this->asAdminUser()->json('POST', 'v1/users/'.$user->id.'/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals($user->fresh()->last_accessed_at->toTimeString(), '01:00:00');

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInDatabase('users', [
            '_id' => $duplicate->id,
            'last_accessed_at' => null,
        ]);
    }

    /**
     * Test language merge logic.
     * POST /resets
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

        $this->asAdminUser()->json('POST', 'v1/users/'.$user->id.'/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->assertEquals($user->fresh()->language, 'yo');

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInDatabase('users', [
            '_id' => $duplicate->id,
            'language' => null,
            'last_accessed_at' => null,
        ]);
    }
}
