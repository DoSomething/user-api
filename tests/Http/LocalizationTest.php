<?php

class LocalizationTest extends TestCase
{

    /**
     * Test that the correct Fastly header is applied for the given header.
     */
    public function testAppLocale()
    {
        $this->get('/', ['X-FASTLY-COUNTRY-CODE' => 'MX']);
        $this->assertEquals(App::getLocale(), 'es-mx');
    }
}
