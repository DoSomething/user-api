<?php

namespace Tests\LegacyHttp;

use App\Models\User;
use Tests\BrowserKitTestCase;

class ProfileTest extends BrowserKitTestCase
{
    /**
     * Test that a user can see their own profile.
     *
     * @test
     */
    public function testGetProfile()
    {
        $user = factory(User::class)->create([
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
        ]);

        $this->asUser($user, ['user'])->get('v1/profile');

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.id', $user->id);
        $this->seeJsonField('data.email', $user->email);
        $this->seeJsonField('data.first_name', $user->first_name);
        $this->seeJsonField('data.last_name', $user->last_name);
    }

    /**
     * Test that a user can modify their own profile.
     *
     * @test
     */
    public function testUpdateProfile()
    {
        $user = factory(User::class)->create([
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'drupal_id' => 123456,
            'role' => 'user',
        ]);

        $this->asUser($user, ['user', 'write'])->json('POST', 'v1/profile', [
            'mobile' => '(555) 123-4567',
            'language' => 'en',
            'drupal_id' => 666666,
            'role' => 'admin',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.id', $user->id);
        $this->seeJsonField('data.drupal_id', 123456); // shouldn't have changed, field is read-only for users!
        $this->seeJsonField('data.role', 'user'); // shouldn't have changed, field is read-only for users!
        $this->seeJsonField('data.mobile', '5551234567'); // should be normalized!
    }

    /**
     * Test that the write scope is required to update a profile.
     *
     * @test
     */
    public function testUpdateProfileWithoutWriteScope()
    {
        $user = factory(User::class)->create([
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'drupal_id' => 123456,
            'role' => 'user',
        ]);

        $this->asUser($user, ['user'])->json('POST', 'v1/profile', [
            'mobile' => '(555) 123-4567',
            'language' => 'en',
            'drupal_id' => 666666,
            'role' => 'admin',
        ]);

        $this->assertResponseStatus(401);
        $this->assertEquals(
            'Requires the `write` scope.',
            $this->response->decodeResponseJson()['hint'],
        );
    }
}
