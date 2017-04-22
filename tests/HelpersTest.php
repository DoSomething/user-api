<?php

use Carbon\Carbon;
use Northstar\Models\Client;

class HelpersTest extends TestCase
{
    /** @test */
    public function testFormatDate()
    {
        // It should format strings that PHP can parse as DateTimes.
        $this->assertEquals(format_date('10/25/1990'), 'Oct 25, 1990');
        $this->assertEquals(format_date('1990-10-25'), 'Oct 25, 1990');

        // It should also format Carbon objects.
        $carbonDate = Carbon::create(1990, 10, 25);
        $this->assertEquals(format_date($carbonDate), 'Oct 25, 1990');

        // It should return null if null is passed.
        $this->assertEquals(format_date(null), null);
    }

    /** @test */
    public function testRouteHasAttachedMiddleware()
    {
        $this->get('/login');

        // It should be able to check if in a middleware group.
        $this->assertTrue(has_middleware('web'));
        $this->assertFalse(has_middleware('api'));

        // ...or just if it has any middleware at all!
        $this->assertTrue(has_middleware());
    }

    /** @test */
    public function testGetClientIdForWebRequests()
    {
        $this->get('/register');

        // Web requests should report as 'northstar'.
        $this->assertEquals(client_id(), 'northstar');
    }

    /** @test */
    public function testGetClientIdForLegacyKeys()
    {
        $client = Client::create(['client_id' => 'legacy_client', 'scope' => 'user']);
        $this->withLegacyApiKey($client)->json('POST', '/v1/auth/register', [
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password,
        ]);

        $this->assertEquals(client_id(), 'legacy_client');
    }

    /** @test */
    public function getClientIdForOAuth()
    {
        $this->asNormalUser()->json('POST', '/v1/auth/register', [
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password,
        ]);

        $this->assertEquals(client_id(), 'phpunit');
    }
}
