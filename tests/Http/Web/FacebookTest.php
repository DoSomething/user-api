<?php

class FacebookTest extends TestCase
{

    /**
     * Test that a user is redirected to Facebook
     * @expectedException \Illuminate\Foundation\Testing\HttpException
     * @expectedExceptionMessageRegExp /www\.facebook\.com/
     */
    public function testFacebookRedirect()
    {
        $this->visit('/facebook/continue');
        $this->assertRedirectedTo('https://www.facebook.com/');
    }

}
