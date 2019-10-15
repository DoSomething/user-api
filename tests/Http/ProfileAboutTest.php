<?php

use Northstar\Models\User;
use Northstar\Models\Client;

class ProfileAboutTest extends BrowserKitTestCase
{
    /**
     * Test that users can edit their preferences page in their profile.
     */
    public function updateProfilePreferences()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/about')
            ->see('Complete Your Profile');
            

    }

}


//rename this for profile about
//create another file for profile subscriptions
//recreate all the registration tests using new reg fields
//list out test cases for subscriptions and cause pages for edge cases etc