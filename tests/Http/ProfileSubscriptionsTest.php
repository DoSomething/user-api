<?php

class ProfileSubscriptionsTest extends BrowserKitTestCase
{
    /**
     * Test that users can reach the profile subscriptions page
     */
    public function testViewingProfileSubscriptions()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/subscriptions')
            ->see('Choose your contact method');
    }

    /**
     * Test that users can update their contect methods successfully
     */
    public function testUpdatingContactFields()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/subscriptions')
             ->type('(555) 555-5555', 'mobile')
             ->check('email_subscription_topics[1]')
             ->check('email_subscription_topics[3]')
             ->press('Finish')
             ->followRedirects();
    }

    /**
     * Test that users can move to the next step of registration without updating any fields
     */
    public function testFinishButtonWithoutUpdates()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/subscriptions')
            ->press('Finish')
            ->seePageIs('/');
    }

    /**
     * Test that users can move to the next step of registration without completing any fields
     */
    public function testSkipButton()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/profile/subscriptions')
            ->click('Skip')
            ->seePageIs('/');
    }

}