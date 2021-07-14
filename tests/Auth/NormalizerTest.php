<?php

namespace Tests\Auth;

use Carbon\Carbon;
use Tests\BrowserKitTestCase;

// @TODO: establish Units & Features directory in test and place this file
// of unit tests in Units.

class NormalizerTest extends BrowserKitTestCase
{
    /**
     * Test that we can normalize dates.
     */
    public function testNormalizeDates()
    {
        $normalized = normalize('dates', [
            'December 25th 2021',
            '2021-10-31',
            '02/14/1990',
        ]);

        $this->assertCount(3, $normalized);
        $this->assertSame(
            '2021-12-25 00:00:00',
            $normalized[0]->toDateTimeString(),
        );
        $this->assertSame(
            '2021-10-31 00:00:00',
            $normalized[1]->toDateTimeString(),
        );
        $this->assertSame(
            '1990-02-14 00:00:00',
            $normalized[2]->toDateTimeString(),
        );
    }

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
