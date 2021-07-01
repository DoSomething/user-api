<?php

namespace Tests\Http;

use Tests\BrowserKitTestCase;

class ProfileAboutTest extends BrowserKitTestCase
{
    /**
     * Test that users can navigate to the complete your profile page.
     */
    public function testViewingProfileAbout()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/about')->see('Complete Your Profile');
    }

    /**
     * Test that users can update their preferences successfully.
     */
    public function testUpdatingPreferenceFields()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/about')
            ->type('10/10/2003', 'birthdate')
            ->select('unregistered', 'voter_registration_status')
            ->check('causes[0]')
            ->uncheck('causes[0]')
            ->check('causes[8]')
            ->check('causes[4]')
            ->press('Next')
            ->seePageIs('/profile/subscriptions');
    }

    /**
     * Test that users will not see any prompts if they select "Yes" on Voter Registration.
     */
    public function testVoterRegistrationStatusPromptHidden()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/about')
            ->select('confirmed', 'voter_registration_status')
            ->dontSee(
                'Not sure? We can help! Take 2 minutes and check your voter registration status with Rock The Vote!',
            )
            ->dontSee(
                'Make your voice heard on the issues that matter to you. Take 2 minutes and register to vote at your current address!',
            );
    }

    /**
     * Test that users can't update their birthday to an invalid date (backend validation).
     */
    public function testBirthdateError()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/about')
            ->type('10/45/2003', 'birthdate')
            ->press('Next')
            ->seePageIs('/profile/about');
    }

    /**
     * Test that users can move to the next step of registration without updating any fields.
     */
    public function testNextButtonWithoutUpdates()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/about')
            ->press('Next')
            ->seePageIs('/profile/subscriptions');
    }

    /**
     * Test that users can move to the next step of registration without completing any fields.
     */
    public function testSkipButton()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/about')
            ->click('Skip')
            ->seePageIs('/profile/subscriptions');
    }
}
