<?php

namespace Tests\Http;

use Illuminate\Support\Facades\App;
use Tests\BrowserKitTestCase;

class LocalizationTest extends BrowserKitTestCase
{
    /**
     * Test that the correct language is applied for the supported header.
     */
    public function testSupportedCountry()
    {
        $this->get('/', ['X-FASTLY-COUNTRY-CODE' => 'MX']);
        $this->assertEquals(App::getLocale(), 'es-mx');
    }

    /**
     * Test that the default language is applied for an unsupported header.
     */
    public function testUnsupportedCountry()
    {
        $this->get('/', ['X-FASTLY-COUNTRY-CODE' => 'FR']);
        $this->assertEquals(App::getLocale(), 'en');
    }
}
