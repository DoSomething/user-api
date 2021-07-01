<?php

namespace Tests\Auth;

use Tests\BrowserKitTestCase;

class NormalizerTest extends BrowserKitTestCase
{
    /**
     * Test that we can normalize the ID field name.
     */
    public function testNormalizeId()
    {
        $credentials = [
            'id' => $this->faker->uuid,
        ];

        $normalized = normalize('credentials', $credentials);

        $this->assertArrayHasKey('_id', $normalized);
        $this->assertArrayNotHasKey('id', $normalized);
        $this->assertSame($credentials['id'], $normalized['_id']);
    }

    /**
     * Test that we can normalize email addresses.
     */
    public function testNormalizeEmail()
    {
        $normalized = normalize('email', 'Kamala.Khan@marvel.com ');

        $this->assertSame('kamala.khan@marvel.com', $normalized);
    }

    /**
     * Test that we can normalize mobile phone numbers.
     */
    public function testNormalizeMobile()
    {
        $normalized = normalize('mobile', '1 (555) 123-4567');

        $this->assertSame('+15551234567', $normalized);
    }

    /**
     * Test that we can normalize an email provided in the 'username' field.
     */
    public function testNormalizeEmailAsUsername()
    {
        $normalized = normalize('credentials', [
            'username' => 'Kamala.Khan@marvel.com ',
        ]);

        $this->assertArrayNotHasKey('username', $normalized);
        $this->assertArrayNotHasKey('mobile', $normalized);

        $this->assertSame('kamala.khan@marvel.com', $normalized['email']);
    }

    /**
     * Test that we can normalize an email provided in the 'username' field.
     */
    public function testNormalizeMobileAsUsername()
    {
        $normalized = normalize('credentials', [
            'username' => '1 (555) 123-4567',
        ]);

        $this->assertArrayNotHasKey('username', $normalized);
        $this->assertArrayNotHasKey('email', $normalized);

        $this->assertSame('+15551234567', $normalized['mobile']);
    }

    /**
     * Test that we can normalize multiple fields.
     */
    public function testNormalizeMultipleFields()
    {
        $normalized = normalize('credentials', [
            '_id' => $this->faker->uuid,
            'mobile' => $this->faker->phoneNumber,
        ]);

        $this->assertArrayHasKey('_id', $normalized);
        $this->assertArrayHasKey('mobile', $normalized);
        $this->assertArrayNotHasKey('email', $normalized);
    }
}
