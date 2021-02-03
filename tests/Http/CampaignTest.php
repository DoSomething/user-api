<?php

use App\Models\Campaign;
use App\Models\GroupType;
use App\Models\Post;
use App\Types\Cause;
use Illuminate\Support\Facades\Artisan;

class CampaignTest extends TestCase
{
    //     /**
//      * Test that a GET request to /api/v3/campaigns returns an index of all campaigns.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testCampaignIndex()
//     {
//         factory(Campaign::class, 5)->create();
//         $response = $this->getJson('api/v3/campaigns');
//         $decodedResponse = $response->decodeResponseJson();
//         $response->assertStatus(200);
//         $this->assertEquals(5, $decodedResponse['meta']['pagination']['count']);
//     }
//     /**
//      * Test that we can filter open or closed campaigns.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testFilteredCampaignIndex()
//     {
//         factory(Campaign::class, 5)->create();
//         factory(Campaign::class, 'closed', 3)->create();
//         $response = $this->getJson('api/v3/campaigns?filter[is_open]=true');
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(5, $decodedResponse['meta']['pagination']['count']);
//         $response = $this->getJson('api/v3/campaigns?filter[is_open]=false');
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(3, $decodedResponse['meta']['pagination']['count']);
//     }
//     /**
//      * Test that we can filter campaigns with an associated Contentful 'Website' entry.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testWebsiteFilteredCampaignIndex()
//     {
//         factory(Campaign::class, 5)->create([
//             'contentful_campaign_id' => '123',
//         ]);
//         factory(Campaign::class, 3)->create();
//         $response = $this->getJson('api/v3/campaigns?filter[has_website]=true');
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(5, $decodedResponse['meta']['pagination']['count']);
//         $response = $this->getJson(
//             'api/v3/campaigns?filter[has_website]=false',
//         );
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(3, $decodedResponse['meta']['pagination']['count']);
//     }
//     /**
//      * Test that we can filter campaigns by cause.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testCauseFilteredCampaignIndex()
//     {
//         $causes = Cause::all();
//         // Let's test against pairs of three causes each so that we have a first, last, and middle cause
//         // (ensuring we're testing our filtering logic against surrounding commas).
//         $campaignWithFirstThreeCauses = factory(Campaign::class, 1)->create([
//             'cause' => array_slice($causes, 0, 3),
//         ]);
//         $campaignWithLastThreeCauses = factory(Campaign::class, 1)->create([
//             'cause' => array_slice($causes, -3),
//         ]);
//         foreach (array_slice($causes, 0, 3) as $index => $cause) {
//             $response = $this->getJson(
//                 'api/v3/campaigns?filter[cause]=' . $cause,
//             );
//             $decodedResponse = $response->decodeResponseJson();
//             $this->assertEquals(
//                 1,
//                 $decodedResponse['meta']['pagination']['count'],
//             );
//             $this->assertEquals(
//                 $campaignWithFirstThreeCauses->first()['id'],
//                 $decodedResponse['data'][0]['id'],
//             );
//         }
//         // Test that we can filter by multiple causes.
//         $response = $this->getJson(
//             'api/v3/campaigns?filter[cause]=' .
//                 implode(',', array_slice($causes, 0, 3)),
//         );
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(1, $decodedResponse['meta']['pagination']['count']);
//         $this->assertEquals(
//             $campaignWithFirstThreeCauses->first()['id'],
//             $decodedResponse['data'][0]['id'],
//         );
//         // Test that invalid causes are rejected by the filter:
//         $response = $this->getJson(
//             'api/v3/campaigns?filter[cause]=this-is-not-a-cause,nor-this!',
//         );
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(0, $decodedResponse['meta']['pagination']['count']);
//     }
//     /**
//      * Test that we can use cursor pagination.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testCampaignCursor()
//     {
//         $campaigns = factory(Campaign::class, 5)->create();
//         // First, let's get the three campaigns with the most pending posts:
//         $endpoint = 'api/v3/campaigns?limit=3';
//         $response = $this->withAdminAccessToken()->getJson($endpoint);
//         $response->assertSuccessful();
//         $json = $response->json();
//         $this->assertCount(3, $json['data']);
//         // Then, we'll use the last post's cursor to fetch the remaining two:
//         $lastCursor = $json['data'][2]['cursor'];
//         $response = $this->withAdminAccessToken()->getJson(
//             $endpoint . '&cursor[after]=' . $lastCursor,
//         );
//         $response->assertSuccessful();
//         $json = $response->json();
//         $this->assertCount(2, $json['data']);
//     }
//     /**
//      * Test that we can use cursor pagination with ordered results.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testCampaignCursorWithOrderBy()
//     {
//         // Create campaigns with varied number of 'pending' posts:
//         $one = $this->createCampaignWithPosts(1);
//         $two = $this->createCampaignWithPosts(2);
//         $three = $this->createCampaignWithPosts(3);
//         $four = $this->createCampaignWithPosts(4);
//         $five = $this->createCampaignWithPosts(5);
//         // We need these counter caches for this to work properly:
//         Artisan::call('rogue:recount');
//         // First, let's get the three campaigns with the most pending posts:
//         $endpoint = 'api/v3/campaigns?orderBy=pending_count,desc&limit=3';
//         $response = $this->withAdminAccessToken()->getJson($endpoint);
//         $response->assertJson([
//             'data' => [0 => ['id' => $five->id, 'pending_count' => 5]],
//         ]);
//         $response->assertJson([
//             'data' => [1 => ['id' => $four->id, 'pending_count' => 4]],
//         ]);
//         $response->assertJson([
//             'data' => [2 => ['id' => $three->id, 'pending_count' => 3]],
//         ]);
//         // Then, we'll use the last post's cursor to fetch the remaining two:
//         $lastCursor = $response->json()['data'][2]['cursor'];
//         $response = $this->withAdminAccessToken()->getJson(
//             $endpoint . '&cursor[after]=' . $lastCursor,
//         );
//         $response->assertJson([
//             'data' => [0 => ['id' => $two->id, 'pending_count' => 2]],
//         ]);
//         $response->assertJson([
//             'data' => [1 => ['id' => $one->id, 'pending_count' => 1]],
//         ]);
//     }
//     /**
//      * Test that a GET request to /api/v3/campaigns/:campaign_id returns the intended campaign.
//      *
//      * GET /api/v3/campaigns/:campaign_id
//      * @return void
//      */
//     public function testCampaignShow()
//     {
//         // Create 5 campaigns.
//         factory(Campaign::class, 5)->create();
//         // Create 1 specific campaign to search for.
//         $campaign = factory(Campaign::class)->create();
//         $response = $this->getJson('api/v3/campaigns/' . $campaign->id);
//         $decodedResponse = $response->decodeResponseJson();
//         $response->assertStatus(200);
//         $this->assertEquals($campaign->id, $decodedResponse['data']['id']);
//     }
//     /**
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
//      * Test for updating a campaign successfully with contentful campaign id.
//      *
//      * PATCH /api/v3/campaigns/:campaign_id
//      * @return void
//      */
//     public function testUpdatingACampaignWithContentfulId()
//     {
//         // Create a campaign to update.
//         $campaign = factory(Campaign::class)->create();
//         // Update the contentful campaign id.
//         $response = $this->withAdminAccessToken()->patchJson(
//             'api/v3/campaigns/' . $campaign->id,
//             [
//                 'contentful_campaign_id' => '123456',
//             ],
//         );
//         // Make sure the campaign update is persisted.
//         $response = $this->getJson('api/v3/campaigns/' . $campaign->id);
//         $response->assertStatus(200);
//         $response->assertJson([
//             'data' => [
//                 'contentful_campaign_id' => '123456',
//             ],
//         ]);
//         $this->assertDatabaseHas('campaigns', [
//             'id' => $campaign->id,
//             'contentful_campaign_id' => '123456',
//         ]);
//     }
//     /**
//      * Test for updating a campaign successfully with a group type id.
//      *
//      * PATCH /api/v3/campaigns/:campaign_id
//      * @return void
//      */
//     public function testUpdatingACampaignWithGroupTypeId()
//     {
//         // Create a campaign to update.
//         $campaign = factory(Campaign::class)->create();
//         // Create a GroupType
//         $groupType = factory(GroupType::class)->create();
//         // Update the group type id.
//         $response = $this->withAdminAccessToken()->patchJson(
//             'api/v3/campaigns/' . $campaign->id,
//             [
//                 'group_type_id' => $groupType->id,
//             ],
//         );
//         // Make sure the campaign update is persisted.
//         $response = $this->getJson('api/v3/campaigns/' . $campaign->id);
//         $response->assertStatus(200);
//         $response->assertJson([
//             'data' => [
//                 'group_type_id' => $groupType->id,
//             ],
//         ]);
//         $this->assertDatabaseHas('campaigns', [
//             'id' => $campaign->id,
//             'group_type_id' => $groupType->id,
//         ]);
//     }
//     /**
//      * Test for updating a campaign with invalid status.
//      *
//      * PATCH /api/v3/campaigns/:campaign_id
//      * @return void
//      */
//     public function testUpdatingACampaignWithInvalidStatus()
//     {
//         // Create a campaign to update.
//         $campaign = factory(Campaign::class)->create();
//         $response = $this->withAdminAccessToken()->patchJson(
//             'api/v3/campaigns/' . $campaign->id,
//             [
//                 'contentful_campaign_id' => 123456, // This should be a string
//             ],
//         );
//         $response->assertStatus(422);
//         $response->assertJsonValidationErrors(['contentful_campaign_id']);
//     }
//     /**
//      * Test for updating a campaign with invalid status.
//      *
//      * PATCH /api/v3/campaigns/:campaign_id
//      * @return void
//      */
//     public function testUpdatingACampaignWithInvalidStatusWithGroupTypeId()
//     {
//         // Create a campaign to update.
//         $campaign = factory(Campaign::class)->create();
//         $response = $this->withAdminAccessToken()->patchJson(
//             'api/v3/campaigns/' . $campaign->id,
//             [
//                 'group_type_id' => 'four', // This should be an integer
//             ],
//         );
//         $response->assertStatus(422);
//         $response->assertJsonValidationErrors(['group_type_id']);
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
//     /**
//      * Create a campaign with the given number of pending posts.
//      *
//      * @return Campaign
//      */
//     public function createCampaignWithPosts($numberOfPosts)
//     {
//         $campaign = factory(Campaign::class)->create();
//         factory(Post::class, $numberOfPosts)->create([
//             'campaign_id' => $campaign->id,
//         ]);
//         return $campaign;
//     }
// use App\Models\Campaign;
// use App\Models\GroupType;
// use App\Models\Post;
// use App\Types\Cause;
// use Illuminate\Support\Facades\Artisan;
// use Tests\TestCase;
// class CampaignTest extends Testcase
// {
//     /**
//      * Test that a POST request to /campaigns creates a new campaign.
//      *
//      * POST /campaigns
//      * @return void
//      */
//     public function testCreatingACampaign()
//     {
//         // Create a campaign.
//         $firstCampaignTitle = $this->faker->sentence;
//         $firstCampaignStartDate = $this->faker->date($format = 'm/d/Y');
//         // Make sure the end date is after the start date.
//         $firstCampaignEndDate = date(
//             'm/d/Y',
//             strtotime('+3 months', strtotime($firstCampaignStartDate)),
//         );
//         // Create a GroupType
//         $groupType = factory(GroupType::class)->create();
//         $this->actingAsAdmin()->postJson('campaigns', [
//             'internal_title' => $firstCampaignTitle,
//             'cause' => ['animal-welfare'],
//             'impact_doc' => 'https://www.google.com',
//             'start_date' => $firstCampaignStartDate,
//             'end_date' => $firstCampaignEndDate,
//             'group_type_id' => $groupType->id,
//         ]);
//         // Make sure the campaign is persisted.
//         $this->assertDatabaseHas('campaigns', [
//             'internal_title' => $firstCampaignTitle,
//         ]);
//         // Try to create a second campaign with the same title and make sure it doesn't duplicate.
//         $this->actingAsAdmin()->postJson('campaigns', [
//             'internal_title' => $firstCampaignTitle,
//         ]);
//         $response = $this->getJson('api/v3/campaigns');
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(1, $decodedResponse['meta']['pagination']['count']);
//     }
//     /**
//      * Test that a GET request to /api/v3/campaigns returns an index of all campaigns.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testCampaignIndex()
//     {
//         factory(Campaign::class, 5)->create();
//         $response = $this->getJson('api/v3/campaigns');
//         $decodedResponse = $response->decodeResponseJson();
//         $response->assertStatus(200);
//         $this->assertEquals(5, $decodedResponse['meta']['pagination']['count']);
//     }
//     /**
//      * Test that we can filter open or closed campaigns.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testFilteredCampaignIndex()
//     {
//         factory(Campaign::class, 5)->create();
//         factory(Campaign::class, 'closed', 3)->create();
//         $response = $this->getJson('api/v3/campaigns?filter[is_open]=true');
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(5, $decodedResponse['meta']['pagination']['count']);
//         $response = $this->getJson('api/v3/campaigns?filter[is_open]=false');
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(3, $decodedResponse['meta']['pagination']['count']);
//     }
//     /**
//      * Test that we can filter campaigns with an associated Contentful 'Website' entry.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testWebsiteFilteredCampaignIndex()
//     {
//         factory(Campaign::class, 5)->create([
//             'contentful_campaign_id' => '123',
//         ]);
//         factory(Campaign::class, 3)->create();
//         $response = $this->getJson('api/v3/campaigns?filter[has_website]=true');
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(5, $decodedResponse['meta']['pagination']['count']);
//         $response = $this->getJson(
//             'api/v3/campaigns?filter[has_website]=false',
//         );
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(3, $decodedResponse['meta']['pagination']['count']);
//     }
//     /**
//      * Test that we can filter campaigns by cause.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testCauseFilteredCampaignIndex()
//     {
//         $causes = Cause::all();
//         // Let's test against pairs of three causes each so that we have a first, last, and middle cause
//         // (ensuring we're testing our filtering logic against surrounding commas).
//         $campaignWithFirstThreeCauses = factory(Campaign::class, 1)->create([
//             'cause' => array_slice($causes, 0, 3),
//         ]);
//         $campaignWithLastThreeCauses = factory(Campaign::class, 1)->create([
//             'cause' => array_slice($causes, -3),
//         ]);
//         foreach (array_slice($causes, 0, 3) as $index => $cause) {
//             $response = $this->getJson(
//                 'api/v3/campaigns?filter[cause]=' . $cause,
//             );
//             $decodedResponse = $response->decodeResponseJson();
//             $this->assertEquals(
//                 1,
//                 $decodedResponse['meta']['pagination']['count'],
//             );
//             $this->assertEquals(
//                 $campaignWithFirstThreeCauses->first()['id'],
//                 $decodedResponse['data'][0]['id'],
//             );
//         }
//         // Test that we can filter by multiple causes.
//         $response = $this->getJson(
//             'api/v3/campaigns?filter[cause]=' .
//                 implode(',', array_slice($causes, 0, 3)),
//         );
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(1, $decodedResponse['meta']['pagination']['count']);
//         $this->assertEquals(
//             $campaignWithFirstThreeCauses->first()['id'],
//             $decodedResponse['data'][0]['id'],
//         );
//         // Test that invalid causes are rejected by the filter:
//         $response = $this->getJson(
//             'api/v3/campaigns?filter[cause]=this-is-not-a-cause,nor-this!',
//         );
//         $decodedResponse = $response->decodeResponseJson();
//         $this->assertEquals(0, $decodedResponse['meta']['pagination']['count']);
//     }
//     /**
//      * Test that we can use cursor pagination.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testCampaignCursor()
//     {
//         $campaigns = factory(Campaign::class, 5)->create();
//         // First, let's get the three campaigns with the most pending posts:
//         $endpoint = 'api/v3/campaigns?limit=3';
//         $response = $this->withAdminAccessToken()->getJson($endpoint);
//         $response->assertSuccessful();
//         $json = $response->json();
//         $this->assertCount(3, $json['data']);
//         // Then, we'll use the last post's cursor to fetch the remaining two:
//         $lastCursor = $json['data'][2]['cursor'];
//         $response = $this->withAdminAccessToken()->getJson(
//             $endpoint . '&cursor[after]=' . $lastCursor,
//         );
//         $response->assertSuccessful();
//         $json = $response->json();
//         $this->assertCount(2, $json['data']);
//     }
//     /**
//      * Test that we can use cursor pagination with ordered results.
//      *
//      * GET /api/v3/campaigns
//      * @return void
//      */
//     public function testCampaignCursorWithOrderBy()
//     {
//         // Create campaigns with varied number of 'pending' posts:
//         $one = $this->createCampaignWithPosts(1);
//         $two = $this->createCampaignWithPosts(2);
//         $three = $this->createCampaignWithPosts(3);
//         $four = $this->createCampaignWithPosts(4);
//         $five = $this->createCampaignWithPosts(5);
//         // We need these counter caches for this to work properly:
//         Artisan::call('rogue:recount');
//         // First, let's get the three campaigns with the most pending posts:
//         $endpoint = 'api/v3/campaigns?orderBy=pending_count,desc&limit=3';
//         $response = $this->withAdminAccessToken()->getJson($endpoint);
//         $response->assertJson([
//             'data' => [0 => ['id' => $five->id, 'pending_count' => 5]],
//         ]);
//         $response->assertJson([
//             'data' => [1 => ['id' => $four->id, 'pending_count' => 4]],
//         ]);
//         $response->assertJson([
//             'data' => [2 => ['id' => $three->id, 'pending_count' => 3]],
//         ]);
//         // Then, we'll use the last post's cursor to fetch the remaining two:
//         $lastCursor = $response->json()['data'][2]['cursor'];
//         $response = $this->withAdminAccessToken()->getJson(
//             $endpoint . '&cursor[after]=' . $lastCursor,
//         );
//         $response->assertJson([
//             'data' => [0 => ['id' => $two->id, 'pending_count' => 2]],
//         ]);
//         $response->assertJson([
//             'data' => [1 => ['id' => $one->id, 'pending_count' => 1]],
//         ]);
//     }
//     /**
//      * Test that a GET request to /api/v3/campaigns/:campaign_id returns the intended campaign.
//      *
//      * GET /api/v3/campaigns/:campaign_id
//      * @return void
//      */
//     public function testCampaignShow()
//     {
//         // Create 5 campaigns.
//         factory(Campaign::class, 5)->create();
//         // Create 1 specific campaign to search for.
//         $campaign = factory(Campaign::class)->create();
//         $response = $this->getJson('api/v3/campaigns/' . $campaign->id);
//         $decodedResponse = $response->decodeResponseJson();
//         $response->assertStatus(200);
//         $this->assertEquals($campaign->id, $decodedResponse['data']['id']);
//     }
//     /**
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
//      * Test for updating a campaign successfully with contentful campaign id.
//      *
//      * PATCH /api/v3/campaigns/:campaign_id
//      * @return void
//      */
//     public function testUpdatingACampaignWithContentfulId()
//     {
//         // Create a campaign to update.
//         $campaign = factory(Campaign::class)->create();
//         // Update the contentful campaign id.
//         $response = $this->withAdminAccessToken()->patchJson(
//             'api/v3/campaigns/' . $campaign->id,
//             [
//                 'contentful_campaign_id' => '123456',
//             ],
//         );
//         // Make sure the campaign update is persisted.
//         $response = $this->getJson('api/v3/campaigns/' . $campaign->id);
//         $response->assertStatus(200);
//         $response->assertJson([
//             'data' => [
//                 'contentful_campaign_id' => '123456',
//             ],
//         ]);
//         $this->assertDatabaseHas('campaigns', [
//             'id' => $campaign->id,
//             'contentful_campaign_id' => '123456',
//         ]);
//     }
//     /**
//      * Test for updating a campaign successfully with a group type id.
//      *
//      * PATCH /api/v3/campaigns/:campaign_id
//      * @return void
//      */
//     public function testUpdatingACampaignWithGroupTypeId()
//     {
//         // Create a campaign to update.
//         $campaign = factory(Campaign::class)->create();
//         // Create a GroupType
//         $groupType = factory(GroupType::class)->create();
//         // Update the group type id.
//         $response = $this->withAdminAccessToken()->patchJson(
//             'api/v3/campaigns/' . $campaign->id,
//             [
//                 'group_type_id' => $groupType->id,
//             ],
//         );
//         // Make sure the campaign update is persisted.
//         $response = $this->getJson('api/v3/campaigns/' . $campaign->id);
//         $response->assertStatus(200);
//         $response->assertJson([
//             'data' => [
//                 'group_type_id' => $groupType->id,
//             ],
//         ]);
//         $this->assertDatabaseHas('campaigns', [
//             'id' => $campaign->id,
//             'group_type_id' => $groupType->id,
//         ]);
//     }
//     /**
//      * Test for updating a campaign with invalid status.
//      *
//      * PATCH /api/v3/campaigns/:campaign_id
//      * @return void
//      */
//     public function testUpdatingACampaignWithInvalidStatus()
//     {
//         // Create a campaign to update.
//         $campaign = factory(Campaign::class)->create();
//         $response = $this->withAdminAccessToken()->patchJson(
//             'api/v3/campaigns/' . $campaign->id,
//             [
//                 'contentful_campaign_id' => 123456, // This should be a string
//             ],
//         );
//         $response->assertStatus(422);
//         $response->assertJsonValidationErrors(['contentful_campaign_id']);
//     }
//     /**
//      * Test for updating a campaign with invalid status.
//      *
//      * PATCH /api/v3/campaigns/:campaign_id
//      * @return void
//      */
//     public function testUpdatingACampaignWithInvalidStatusWithGroupTypeId()
//     {
//         // Create a campaign to update.
//         $campaign = factory(Campaign::class)->create();
//         $response = $this->withAdminAccessToken()->patchJson(
//             'api/v3/campaigns/' . $campaign->id,
//             [
//                 'group_type_id' => 'four', // This should be an integer
//             ],
//         );
//         $response->assertStatus(422);
//         $response->assertJsonValidationErrors(['group_type_id']);
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
//     /**
//      * Create a campaign with the given number of pending posts.
//      *
//      * @return Campaign
//      */
//     public function createCampaignWithPosts($numberOfPosts)
//     {
//         $campaign = factory(Campaign::class)->create();
//         factory(Post::class, $numberOfPosts)->create([
//             'campaign_id' => $campaign->id,
//         ]);
//         return $campaign;
//     }
}
