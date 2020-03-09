<?php

use Northstar\Models\User;

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
     * Test that users can update their contact methods successfully
     */
    public function testUpdatingContactFields()
    {
        $user = factory(User::class)->create([
            'sms_status' => null,
        ]);

        $this->be($user, 'web');

        $this->visit('/profile/subscriptions')
             ->type('(123) 456-7890', 'mobile')
             ->select('less', 'sms_status')
             ->check('email_subscription_topics[1]')
             ->check('email_subscription_topics[3]')
             ->press('Finish');

        $updatedUser = $user->fresh();

        $this->assertEquals('less', $updatedUser->sms_status);
    }

      /**
     * Test that users can update their contact methods successfully
     */
    public function testUpdatingContactFieldsWithAutoSelectSMS()
    {
        $user = factory(User::class)->create([
            'sms_status' => null,
        ]);

        $this->be($user, 'web');

        $this->visit('/profile/subscriptions')
             ->type('(123) 456-7890', 'mobile')
             ->check('email_subscription_topics[1]')
             ->check('email_subscription_topics[2]')
             ->press('Finish');

        $updatedUser = $user->fresh();

        $this->assertEquals('active', $updatedUser->sms_status);
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
