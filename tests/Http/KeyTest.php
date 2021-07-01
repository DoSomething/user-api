<?php

namespace Tests\Http;

use Tests\BrowserKitTestCase;

class KeyTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_display_public_key()
    {
        $this->get('v2/keys');

        $this->assertResponseOk();
        $this->seeJsonStructure([
            'keys' => [
                '*' => ['kty', 'e', 'n', 'kid'],
            ],
        ]);
    }
}
