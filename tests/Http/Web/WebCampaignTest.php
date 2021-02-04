<?php

use App\Models\GroupType;
use App\Models\User;

class WebCampaignTest extends TestCase
{
    /**
     * Test that a POST request to /ca8mpaigns creates a new campaign.
     *
     * POST /campaigns
     * @return void
     */
    public function testCreatingACampaign()
    {
        $this->withoutExceptionHandling(); // @TODO: delete!

        $firstCampaignTitle = $this->faker->sentence;

        // Make sure the end date is after the start date.
        $firstCampaignStartDate = $this->faker->date($format = 'm/d/Y');
        $firstCampaignEndDate = date(
            'm/d/Y',
            strtotime('+3 months', strtotime($firstCampaignStartDate)),
        );

        $groupType = factory(GroupType::class)->create();

        $admin = $this->makeAuthAdminUser();

        $response = $this->actingAs($admin)->post('/campaigns', [
            'internal_title' => $firstCampaignTitle,
            'cause' => ['animal-welfare'],
            'impact_doc' => 'https://www.google.com',
            'start_date' => $firstCampaignStartDate,
            'end_date' => $firstCampaignEndDate,
            'group_type_id' => $groupType->id,
        ]);

        $response->dump();

        dd([
            $firstCampaignTitle,
            $firstCampaignStartDate,
            $firstCampaignEndDate,
        ]);
        // Make sure the campaign is persisted.
        $this->assertDatabaseHas('campaigns', [
            'internal_title' => $firstCampaignTitle,
        ]);
        // Try to create a second campaign with the same title and make sure it doesn't duplicate.
        $this->asAdminUser()->postJson('campaigns', [
            'internal_title' => $firstCampaignTitle,
        ]);
        $response = $this->getJson('api/v3/campaigns');
        $decodedResponse = $response->decodeResponseJson();
        $this->assertEquals(1, $decodedResponse['meta']['pagination']['count']);
    }

    // /**
    //      * Test that a PATCH request to /campaigns/:campaign_id updates a campaign.
    //      *
    //      * PATCH /campaigns/:campaign_id
    //      * @return void
    //      */
    //     public function testUpdatingACampaign()
    //     {
    //         // Create a campaign to update.
    //         $campaign = factory(Campaign::class)->create();
    //         // Update the title.
    //         $response = $this->actingAsAdmin()->patch(
    //             'campaigns/' . $campaign->id,
    //             [
    //                 'internal_title' => 'Updated Title',
    //                 'impact_doc' => 'https://www.bing.com/',
    //                 'cause' => ['lgbtq-rights'],
    //                 'start_date' => '1/1/2018',
    //             ],
    //         );
    //         // Make sure the campaign update is persisted.
    //         $response = $this->getJson('api/v3/campaigns/' . $campaign->id);
    //         $response->assertStatus(200);
    //         $response->assertJson([
    //             'data' => [
    //                 'internal_title' => 'Updated Title',
    //                 'cause' => ['lgbtq-rights'],
    //                 'start_date' => '2018-01-01T00:00:00-05:00',
    //             ],
    //         ]);
    //     }
    //     /**
    //      * Test that a DELETE request to /campaigns/:campaign_id deletes a campaign.
    //      *
    //      * DELETE /campaigns/:campaign_id
    //      * @return void
    //      */
    //     public function testDeleteACampaign()
    //     {
    //         // Create a campaign to delete.
    //         $campaign = factory(Campaign::class)->create();
    //         // Delete the campaign.
    //         $this->actingAsAdmin()->deleteJson('campaigns/' . $campaign->id);
    //         // Make sure the campaign is deleted.
    //         $response = $this->getJson('api/v3/campaigns/' . $campaign->id);
    //         $decodedResponse = $response->decodeResponseJson();
    //         $response->assertStatus(404);
    //         $this->assertEquals(
    //             'That resource could not be found.',
    //             $decodedResponse['message'],
    //         );
    //     }
}
