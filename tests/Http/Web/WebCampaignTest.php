<?php

namespace Tests\Http\Web;

use App\Models\Campaign;
use App\Models\GroupType;
use App\Models\User;
use Tests\TestCase;

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
        $admin = factory(User::class)->states('admin')->create();

        $firstCampaignTitle = $this->faker->sentence;

        // Make sure the end date is after the start date.
        $firstCampaignStartDate = $this->faker->date($format = 'm/d/Y');
        $firstCampaignEndDate = date(
            'm/d/Y',
            strtotime('+3 months', strtotime($firstCampaignStartDate)),
        );

        $groupType = factory(GroupType::class)->create();

        $this->actingAs($admin, 'web')->post('/admin/campaigns', [
            'internal_title' => $firstCampaignTitle,
            'cause' => ['animal-welfare'],
            'impact_doc' => 'https://www.google.com',
            'start_date' => $firstCampaignStartDate,
            'end_date' => $firstCampaignEndDate,
            'group_type_id' => $groupType->id,
        ]);

        // Make sure the campaign is persisted.
        $this->assertMysqlDatabaseHas('campaigns', [
            'internal_title' => $firstCampaignTitle,
        ]);

        // Try to create a second campaign with the same title and make sure it doesn't duplicate.
        $this->actingAs($admin, 'web')->postJson('/admin/campaigns', [
            'internal_title' => $firstCampaignTitle,
        ]);

        $response = $this->getJson('api/v3/campaigns');

        $response->assertJsonPath('meta.pagination.count', 1);
    }

    /**
     * Test that a PATCH request to /campaigns/:campaign_id updates a campaign.
     *
     * PATCH /campaigns/:campaign_id
     * @return void
     */
    public function testUpdatingACampaign()
    {
        $admin = factory(User::class)->states('admin')->create();

        $campaign = factory(Campaign::class)->create();

        // Update the title.
        $response = $this->actingAs($admin, 'web')->patch(
            "/admin/campaigns/$campaign->id",
            [
                'internal_title' => 'Updated Title',
                'impact_doc' => 'https://www.bing.com/',
                'cause' => ['lgbtq-rights'],
                'start_date' => '1/1/2018',
            ],
        );

        // Make sure the campaign update is persisted.
        $response = $this->getJson("api/v3/campaigns/$campaign->id");

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'internal_title' => 'Updated Title',
                'cause' => ['lgbtq-rights'],
                'start_date' => '2018-01-01T00:00:00-05:00',
            ],
        ]);
    }

    /**
     * Test that a DELETE request to /campaigns/:campaign_id deletes a campaign.
     *
     * DELETE /campaigns/:campaign_id
     * @return void
     */
    public function testDeleteACampaign()
    {
        $admin = factory(User::class)->states('admin')->create();

        $campaign = factory(Campaign::class)->create();

        // Delete the campaign.
        $this->actingAs($admin, 'web')->deleteJson(
            "/admin/campaigns/$campaign->id",
        );

        // Make sure the campaign is deleted.
        $response = $this->getJson("api/v3/campaigns/$campaign->id");

        $response->assertNotFound();
    }
}
