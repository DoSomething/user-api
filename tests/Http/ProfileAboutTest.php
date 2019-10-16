<?php

use Northstar\Models\User;
use Northstar\Models\Client;

class ProfileAboutTest extends BrowserKitTestCase
{
    /**
     * Test that users can navigate to the complete your profile page
     * 
     */
    public function testViewingProfileAbout()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/about')
            ->see('Complete Your Profile');
            

    }

    /**
     * Test that users can updated their preferences successfully
     * 
     */
    public function testUpdatingPreferenceFields()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/about')
            ->type('10/10/2003', 'birthdate')
            ->select('unregistered', 'voter_registration_status')
            // ->check('bullying')
            ->check('causes')
            ->press('Next')
            ->seePageIs('/profile/subscriptions');
    }

    /**
     * Test that users can updated their preferences successfully
     * 
     */
    public function testNextButtonWithoutUpdates()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/about')
            ->press('Next')
            ->seePageIs('/profile/subscriptions');
    }

}


//rename this for profile about
//create another file for profile subscriptions
//recreate all the registration tests using new reg fields
//list out test cases for subscriptions and cause pages for edge cases etc

// tests to create:
// clicking register and being navigated to this view 
// updating all 3 fields 
// clicking next and being navigated to the subscriptions page 
