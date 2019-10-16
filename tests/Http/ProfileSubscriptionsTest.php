<?php

use Northstar\Models\User;
use Northstar\Models\Client;

class ProfileSubscriptionsTest extends BrowserKitTestCase
{
    /**
     * Test that users can reach the profile subscriptions page
     * 
     */
    public function testViewingProfileSubscriptions()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/subscriptions')
            ->see('Choose your contact method');
            

    }

}

// tests to create:
// clicking next and being navigated to this view 
// updating all fields 
// clicking finish and being navigated to the profile homepage
