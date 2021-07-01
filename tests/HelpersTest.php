<?php

namespace Tests;

use App\Models\Client;
use Carbon\Carbon;

class HelpersTest extends BrowserKitTestCase
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
    public function testIso8601()
    {
        $this->assertEquals(
            '2017-12-15T22:00:00+00:00',
            iso8601('December 15 2017 10:00pm'),
        );
        $this->assertEquals(
            '2017-12-15T22:00:00+00:00',
            iso8601(new Carbon('December 15 2017 10:00pm')),
        );
        $this->assertEquals(null, iso8601(null), 'handles null values safely');
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
    public function testGetClientIdFromWeb()
    {
        $this->get('/register');
        $this->assertEquals(client_id(), 'northstar');
    }

    /** @test */
    public function testGetClientIdFromLegacyHeader()
    {
        $client = Client::create([
            'client_id' => 'legacy_client',
            'scope' => 'user',
        ]);
        $this->withLegacyApiKey($client)->getJson('/status');
        $this->assertEquals(client_id(), 'legacy_client');
    }

    /** @test */
    public function testGetClientIdFromOAuth()
    {
        $this->asNormalUser()->getJson('/status');
        $this->assertEquals(client_id(), 'phpunit');
    }

    /** @test */
    public function testIsDoSomethingDomain()
    {
        $this->assertTrue(
            is_dosomething_domain('https://dosomething.org'),
            'It should recognize our base domain.',
        );
        $this->assertTrue(
            is_dosomething_domain('https://identity.dosomething.org'),
            'It should recognize one of our subdomains.',
        );
        $this->assertTrue(
            is_dosomething_domain(
                'https://www.dosomething.org/campaigns/teens-for-jeans',
            ),
            'It should recognize a nested path.',
        );

        $this->assertFalse(
            is_dosomething_domain('https://www.google.com'),
            'It should reject a non-DoSomething hostname.',
        );
        $this->assertFalse(
            is_dosomething_domain('https://dosomething.org.evil.com'),
            'It should reject a non-DoSomething hostname with valid "prefix".',
        );
        $this->assertFalse(
            is_dosomething_domain('https://www.dontdosomething.org'),
            'It should reject a non-DoSomething hostname with valid "suffix".',
        );
        $this->assertFalse(
            is_dosomething_domain('https://cobalt.io\@admin.dosomething.org'),
            'It should reject a URL with username hack.',
        );
        $this->assertFalse(
            is_dosomething_domain('https://cobalt.io\.admin.dosomething.org'),
            'It should reject hostname with escaped dot.',
        );
    }
}
