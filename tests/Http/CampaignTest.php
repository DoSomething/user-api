<?php

namespace Tests\Http;

use App\Models\Campaign;
use App\Models\GroupType;
use App\Models\Post;
use App\Types\Cause;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    /**
     * Create a campaign with the given number of pending posts.
     *
     * @return Campaign
     */
    public function createCampaignWithPosts($numberOfPosts)
    {
        $campaign = factory(Campaign::class)->create();

        factory(Post::class, $numberOfPosts)->create([
            'campaign_id' => $campaign->id,
        ]);

        return $campaign;
    }

    /**
     * Test that a GET request to /api/v3/campaigns returns an index of all campaigns.
     *
     * GET /api/v3/campaigns
     * @return void
     */
    public function testCampaignIndex()
    {
        factory(Campaign::class, 5)->create();

        $response = $this->getJson('api/v3/campaigns');

        $response->assertOk();
        $response->assertJsonPath('meta.pagination.count', 5);
    }

    /**
     * Test that we can filter open or closed campaigns.
     *
     * GET /api/v3/campaigns
     * @return void
     */
    public function testFilteredCampaignIndex()
    {
        factory(Campaign::class, 5)->create();

        factory(Campaign::class, 3)->states('closed')->create();

        $responseOne = $this->getJson('api/v3/campaigns?filter[is_open]=true');

        $responseOne->assertOk();
        $responseOne->assertJsonPath('meta.pagination.count', 5);

        $responseTwo = $this->getJson('api/v3/campaigns?filter[is_open]=false');

        $responseTwo->assertOk();
        $responseTwo->assertJsonPath('meta.pagination.count', 3);
    }

    /**
     * Test that we can filter campaigns with an associated Contentful 'Website' entry.
     *
     * GET /api/v3/campaigns
     * @return void
     */
    public function testWebsiteFilteredCampaignIndex()
    {
        factory(Campaign::class, 5)->create([
            'contentful_campaign_id' => '123',
        ]);

        factory(Campaign::class, 3)->create();

        $responseOne = $this->getJson(
            'api/v3/campaigns?filter[has_website]=true',
        );

        $responseOne->assertOk();
        $responseOne->assertJsonPath('meta.pagination.count', 5);

        $responseTwo = $this->getJson(
            'api/v3/campaigns?filter[has_website]=false',
        );

        $responseTwo->assertOk();
        $responseTwo->assertJsonPath('meta.pagination.count', 3);
    }

    /**
     * Test that we can filter campaigns by cause.
     *
     * GET /api/v3/campaigns
     * @return void
     */
    public function testCauseFilteredCampaignIndex()
    {
        $causes = Cause::all();

        // Let's test against pairs of three causes each so that we have a first, last, and middle cause
        // (ensuring we're testing our filtering logic against surrounding commas).
        $campaignWithFirstThreeCauses = factory(Campaign::class)->create([
            'cause' => array_slice($causes, 0, 3),
        ]);

        // Add some additional filler campaigns with causes.
        factory(Campaign::class, 1)->create([
            'cause' => array_slice($causes, -3),
        ]);

        foreach (array_slice($causes, 0, 3) as $index => $cause) {
            $response = $this->getJson(
                'api/v3/campaigns?filter[cause]=' . $cause,
            );

            $response->assertOk();
            $response->assertJsonPath('meta.pagination.count', 1);
            $response->assertJsonPath(
                'data.0.id',
                $campaignWithFirstThreeCauses->id,
            );
        }

        // Test that we can filter by multiple causes.
        $responseTwo = $this->getJson(
            'api/v3/campaigns?filter[cause]=' .
                implode(',', array_slice($causes, 0, 3)),
        );

        $responseTwo->assertOk();
        $responseTwo->assertJsonPath('meta.pagination.count', 1);
        $responseTwo->assertJsonPath(
            'data.0.id',
            $campaignWithFirstThreeCauses->id,
        );

        // Test that invalid causes are rejected by the filter:
        $responseThree = $this->getJson(
            'api/v3/campaigns?filter[cause]=this-is-not-a-cause,nor-this!',
        );

        $responseThree->assertOk();
        $responseThree->assertJsonPath('meta.pagination.count', 0);
    }

    /**
     * Test that we can use cursor pagination.
     *
     * GET /api/v3/campaigns
     * @return void
     */
    public function testCampaignCursor()
    {
        factory(Campaign::class, 5)->create();

        // First, let's get the three campaigns with the most pending posts:
        $endpoint = 'api/v3/campaigns?limit=3';

        $responseOne = $this->asAdminUser()->getJson($endpoint);

        $responseOne->assertOk();
        $responseOne->assertJsonCount(3, 'data');

        // Then, we'll use the last post's cursor to fetch the remaining two:
        $lastCursor = $responseOne->json('data.2.cursor');

        $responseTwo = $this->asAdminUser()->getJson(
            $endpoint . '&cursor[after]=' . $lastCursor,
        );

        $responseTwo->assertOk();
        $responseTwo->assertJsonCount(2, 'data');
    }

    /**
     * Test that we can use cursor pagination with ordered results.
     *
     * GET /api/v3/campaigns
     * @return void
     */
    public function testCampaignCursorWithOrderBy()
    {
        // Create campaigns with varied number of 'pending' posts:
        $one = $this->createCampaignWithPosts(1);
        $two = $this->createCampaignWithPosts(2);
        $three = $this->createCampaignWithPosts(3);
        $four = $this->createCampaignWithPosts(4);
        $five = $this->createCampaignWithPosts(5);

        // We need these counter caches for this to work properly:
        Artisan::call('rogue:recount');

        // First, let's get the three campaigns with the most pending posts:
        $endpoint = 'api/v3/campaigns?orderBy=pending_count,desc&limit=3';

        $responseOne = $this->asAdminUser()->getJson($endpoint);

        $responseOne->assertJson([
            'data' => [0 => ['id' => $five->id, 'pending_count' => 5]],
        ]);
        $responseOne->assertJson([
            'data' => [1 => ['id' => $four->id, 'pending_count' => 4]],
        ]);
        $responseOne->assertJson([
            'data' => [2 => ['id' => $three->id, 'pending_count' => 3]],
        ]);

        // Then, we'll use the last post's cursor to fetch the remaining two:
        $lastCursor = $responseOne->json()['data'][2]['cursor'];

        $responseTwo = $this->asAdminUser()->getJson(
            $endpoint . '&cursor[after]=' . $lastCursor,
        );

        $responseTwo->assertJson([
            'data' => [0 => ['id' => $two->id, 'pending_count' => 2]],
        ]);
        $responseTwo->assertJson([
            'data' => [1 => ['id' => $one->id, 'pending_count' => 1]],
        ]);
    }

    /**
     * Test that a GET request to /api/v3/campaigns/:campaign_id returns the intended campaign.
     *
     * GET /api/v3/campaigns/:campaign_id
     * @return void
     */
    public function testCampaignShow()
    {
        factory(Campaign::class, 5)->create();

        // Create 1 specific campaign to search for.
        $campaign = factory(Campaign::class)->create();

        $response = $this->getJson('api/v3/campaigns/' . $campaign->id);

        $response->assertOk();
        $response->assertJsonPath('data.id', $campaign->id);
    }

    /**
     * Test for updating a campaign successfully with contentful campaign id.
     *
     * PATCH /api/v3/campaigns/:campaign_id
     * @return void
     */
    public function testUpdatingACampaignWithContentfulId()
    {
        $campaign = factory(Campaign::class)->create();

        // Update the contentful campaign id.
        $response = $this->asAdminUser()->patchJson(
            'api/v3/campaigns/' . $campaign->id,
            [
                'contentful_campaign_id' => '123456',
            ],
        );

        // Make sure the campaign update is persisted.
        $response = $this->getJson('api/v3/campaigns/' . $campaign->id);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'contentful_campaign_id' => '123456',
            ],
        ]);
        $this->assertMysqlDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'contentful_campaign_id' => '123456',
        ]);
    }

    /**
     * Test for updating a campaign successfully with a group type id.
     *
     * PATCH /api/v3/campaigns/:campaign_id
     * @return void
     */
    public function testUpdatingACampaignWithGroupTypeId()
    {
        $campaign = factory(Campaign::class)->create();

        $groupType = factory(GroupType::class)->create();

        // Update the group type id.
        $response = $this->asAdminUser()->patchJson(
            'api/v3/campaigns/' . $campaign->id,
            [
                'group_type_id' => $groupType->id,
            ],
        );

        // Make sure the campaign update is persisted.
        $response = $this->getJson('api/v3/campaigns/' . $campaign->id);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'group_type_id' => $groupType->id,
            ],
        ]);
        $this->assertMysqlDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'group_type_id' => $groupType->id,
        ]);
    }

    /**
     * Test for updating a campaign with invalid status.
     *
     * PATCH /api/v3/campaigns/:campaign_id
     * @return void
     */
    public function testUpdatingACampaignWithInvalidStatus()
    {
        $campaign = factory(Campaign::class)->create();

        $response = $this->asAdminUser()->patchJson(
            'api/v3/campaigns/' . $campaign->id,
            [
                'contentful_campaign_id' => 123456, // This should be a string
            ],
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contentful_campaign_id']);
    }

    /**
     * Test for updating a campaign with invalid status.
     *
     * PATCH /api/v3/campaigns/:campaign_id
     * @return void
     */
    public function testUpdatingACampaignWithInvalidStatusWithGroupTypeId()
    {
        $campaign = factory(Campaign::class)->create();

        $response = $this->asAdminUser()->patchJson(
            'api/v3/campaigns/' . $campaign->id,
            [
                'group_type_id' => 'four', // This should be an integer
            ],
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['group_type_id']);
    }
}
