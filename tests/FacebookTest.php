<?php

use Northstar\Models\User;
use Northstar\Services\Facebook;

class ClassName extends TestCase
{
    public function testFacebookVerify()
    {
        $user = User::create(['drupal_id' => '512312']);

        // For testing, we'll mock successful Facebook API response
        $facebook = $this->mock(Facebook::class);
        $facebook->shouldReceive('verifyToken')->once()->andReturn([true]);

        $this->asUserUsingLegacyAuth($user)->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/auth/facebook/validate', [
            'input_token' => 'abc123',
            'facebook_id' => '12345',
        ]);

        // The response should return a 200 OK status code
        $this->assertResponseStatus(200);
        $this->seeJson();
    }
}
