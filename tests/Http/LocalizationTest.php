<?php

class LocalizationTest extends TestCase
{

    /**
     * Test that the correct Fastly header is applied for the given header.
     */
    public function testSupportedCountry()
    {
        $this->get('/', ['X-FASTLY-COUNTRY-CODE' => 'MX']);
        $this->assertEquals(App::getLocale(), 'es-mx');
    }

    public function testUnsupportedCountry()
    {
        $this->get('/', ['X-FASTLY-COUNTRY-CODE' => 'FR']);
        $this->assertEquals(App::getLocale(), 'en');
    }
}
