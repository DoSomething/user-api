<?php

use App as App;
use Northstar\Models\User;

class LocalizationTest extends TestCase
{

    /**
     * Test that the correct Fastly header is applied for the given header.
     */
    public function testAppLocale()
    {
        $this->call('GET', '/', [], [], [], ['HTTP_X_FASTLY_COUNTRY_CODE' => 'MX']);
        $this->assertEquals(App::getLocale(), 'es-mx');
    }
}
