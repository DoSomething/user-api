<?php

use App\Models\GroupType;

class WebCampaignTest extends TestCase
{
    /**
     * Test that a POST request to /campaigns creates a new campaign.
     *
     * POST /campaigns
     * @return void
     */
    public function testCreatingACampaign()
    {
        $this->withoutExceptionHandling(); // @TODO: delete!

        // Create a campaign.
        $firstCampaignTitle = $this->faker->sentence;

        // Make sure the end date is after the start date.
        $firstCampaignStartDate = $this->faker->date($format = 'm/d/Y');
        $firstCampaignEndDate = date(
            'm/d/Y',
            strtotime('+3 months', strtotime($firstCampaignStartDate)),
        );

        // Create a GroupType
        $groupType = factory(GroupType::class)->create();

        $response = $this->asAdminUser()->postJson('/campaigns', [
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
}
