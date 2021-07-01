<?php

namespace Tests\Auth;

use App\Auth\DrupalPasswordHash;
use App\Auth\Registrar;
use App\Models\User;
use Tests\BrowserKitTestCase;

class DrupalPasswordHashTest extends BrowserKitTestCase
{
    /**
     * Tests that Drupal password hasher is working correctly.
     */
    public function testAuthenticatingWithDrupalPassword()
    {
        $user = User::forceCreate([
            'email' => 'dries.buytaert@example.com',
            'drupal_password' =>
                '$S$DOQoztwlGzTeaobeBZKNzlDttbZscuCkkZPv8yeoEvrn26H/GN5b',
        ]);

        // Assert that we can log in with the Drupal-hashed password.
        $success = app(Registrar::class)->validateCredentials($user, [
            'email' => 'dries.buytaert@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($success);

        // Assert user has been updated in the database with a newly hashed password.
        $attributes = $user->fresh()->getAttributes();
        $this->assertArrayNotHasKey('drupal_password', $attributes);
        $this->assertArrayHasKey('password', $attributes);

        // Finally, let's try logging in with the newly hashed password
        $success = app(Registrar::class)->validateCredentials($user, [
            'email' => 'dries.buytaert@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($success);
    }

    /**
     * Test that updating a user's password will re-hash it.
     */
    public function testUpdatingUser()
    {
        $user = User::create([
            'email' => 'acquia.consultant@example.com',
            'drupal_password' =>
                '$S$DOQoztwlGzTeaobeBZKNzlDttbZscuCkkZPv8yeoEvrn26H/GN5b',
        ]);

        $user->password = 'secret';
        $user->save();

        // Assert user has been updated in the database with a newly hashed password.
        $attributes = $user->fresh()->getAttributes();
        $this->assertArrayNotHasKey('drupal_password', $attributes);
        $this->assertArrayHasKey('password', $attributes);

        // Finally, let's try logging in with the newly hashed password
        $success = app(Registrar::class)->validateCredentials($user, [
            'email' => 'acquia.consultant@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($success);
    }

    /**
     * Test the Drupal password hasher against known good/bad
     * password hashes.
     */
    public function testHashesCorrectly()
    {
        // Succeeds if given a good password
        $this->assertTrue(
            DrupalPasswordHash::check(
                'testtest',
                '$S$DYvEbMTfOWVPq5FyHhp70eXBrt8FClzE8bV8RoR8alahwR71PoLE',
            ),
            'Succeeds if given a good password',
        );

        // Fails if given a bad password
        $this->assertFalse(
            DrupalPasswordHash::check(
                'secret',
                '$S$DYvEbMTfOWVPq5FyHhp70eXBrt8FClzE8bV8RoR8alahwR71PoLE',
            ),
            'Fails if given a bad password',
        );

        // Can check older MD5 passwords.
        $this->assertTrue(
            DrupalPasswordHash::check(
                'derpalicious',
                '$P$DxTIL/YfZCdJtFYNh1Ef9ERbMBkuQ91',
            ),
            'Password check succeeds on valid MD5 password.',
        );
        $this->assertTrue(
            DrupalPasswordHash::check(
                'derpalicious',
                '$H$DxTIL/YfZCdJtFYNh1Ef9ERbMBkuQ91',
            ),
            'Password check succeeds on valid MD5 password.',
        );
        $this->assertFalse(
            DrupalPasswordHash::check(
                'nowaytraderjose',
                '$P$DxTIL/YfZCdJtFYNh1Ef9ERbMBkuQ91',
            ),
            'Password check fails on invalid MD5 password.',
        );
        $this->assertFalse(
            DrupalPasswordHash::check(
                'nowaytraderjose',
                '$H$DxTIL/YfZCdJtFYNh1Ef9ERbMBkuQ91',
            ),
            'Password check fails on invalid MD5 password.',
        );
    }
}
