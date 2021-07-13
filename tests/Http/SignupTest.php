<?php

namespace Tests\Http;

use App\Models\Campaign;
use App\Models\Club;
use App\Models\Group;
use App\Models\Post;
use App\Models\Signup;
use App\Models\User;
use App\Observers\SignupObserver;
use App\Services\CustomerIo;
use Illuminate\Support\Str;
use Tests\TestCase;

class SignupTest extends TestCase
{
    /**
     * Test that a POST request to /signups creates a new signup.
     *
     * POST /api/v3/signups
     * @return void
     */
    public function testCreatingASignup()
    {
        $user = factory(User::class)->create();
        $campaignId = $this->faker->randomNumber(4);

        $response = $this->asUser($user)->postJson('api/v3/signups', [
            'campaign_id' => $campaignId,
            'details' => 'affiliate-messaging',
        ]);

        // Make sure we get the 201 Created response
        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'northstar_id' => $user->id,
                'campaign_id' => (string) $campaignId,
                'quantity' => null,
                'source' => 'phpunit',
                'why_participated' => null,
                'group_id' => null,
            ],
        ]);

        // Make sure the signup is persisted.
        $this->assertMysqlDatabaseHas('signups', [
            'northstar_id' => $user->id,
            'campaign_id' => $campaignId,
            'quantity' => null,
            'details' => 'affiliate-messaging',
        ]);
    }

    /**
     * Test that a POST request to /signups creates a new signup and adds a signup badge.
     *
     * POST /api/v3/signups
     * @return void
     */
    public function testAddingFirstSignupBadge()
    {
        $user = factory(User::class)->create();
        $campaignId = $this->faker->randomNumber(4);

        $response = $this->asUser($user)->postJson('api/v3/signups', [
            'campaign_id' => $campaignId,
            'details' => 'badge-testing',
        ]);

        // Make sure we get the 201 Created response
        $response->assertStatus(201);

        $user = $user->fresh();
        $this->assertEquals(['signup'], $user->badges);
    }

    /**
     * Test that a POST request to /signups creates a new signup with group_id if passed.
     *
     * POST /api/v3/signups
     * @return void
     */
    public function testCreatingASignupWithGroupId()
    {
        $user = factory(User::class)->create();
        $group = factory(Group::class)->create();
        $campaignId = $this->faker->randomNumber(4);

        $response = $this->asUser($user)->postJson('api/v3/signups', [
            'campaign_id' => $campaignId,
            'group_id' => $group->id,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'northstar_id' => $user->id,
                'campaign_id' => (string) $campaignId,
                'group_id' => $group->id,
            ],
        ]);
    }

    /**
     * Test that a POST request to /signups creates a new signup with referrer_user_id if passed.
     *
     * POST /api/v3/signups
     * @return void
     */
    public function testCreatingASignupWithReferrerUserId()
    {
        $user = factory(User::class)->create();
        $referrer = factory(User::class)->create();
        $campaignId = $this->faker->randomNumber(4);

        $response = $this->asUser($user)->postJson('api/v3/signups', [
            'campaign_id' => $campaignId,
            'referrer_user_id' => $referrer->id,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'northstar_id' => $user->id,
                'campaign_id' => $campaignId,
                'referrer_user_id' => $referrer->id,
            ],
        ]);
    }

    /**
     * Test that a POST request to /signups creates a new signup with the Northstar user's club_id if set.
     *
     * POST /api/v3/signups
     * @return void
     */
    public function testCreatingASignupForUserWithClubId()
    {
        // Turn on the feature flag for tracking club_ids.
        config(['features.track_club_id' => 'true']);

        $user = factory(User::class)->create();
        $club = factory(Club::class)->create();
        $campaignId = $this->faker->randomNumber(4);

        $this->mock(SignupObserver::class)
            ->makePartial()
            ->shouldReceive('queryForUser')
            ->andReturn([
                'user' => ['clubId' => $club->id],
            ]);

        // Mock the Customer.io API calls.
        $this->mock(CustomerIo::class)
            ->shouldReceive('updateCustomer')
            ->shouldReceive('trackEvent');

        $response = $this->asUser($user)->postJson('api/v3/signups', [
            'campaign_id' => $campaignId,
        ]);
        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'northstar_id' => $user->id,
                'campaign_id' => $campaignId,
                'club_id' => $club->id,
            ],
        ]);
    }

    /**
     * Test that a POST request to /signups doesn't create' new signup with invalid referrer_user_id.
     *
     * POST /api/v3/signups
     * @return void
     */
    public function testCreatingASignupWithInvalidReferrerUserId()
    {
        $user = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create();

        $response = $this->asUser($user)->postJson('api/v3/signups', [
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'hackz12345', // Shockingly, not a real user ID!
        ]);

        $response->assertJsonValidationErrors(['referrer_user_id']);
    }

    /**
     * Test that a POST request to /signups doesn't create a new signup without activity scope.
     *
     * POST /api/v3/signups
     * @return void
     */
    public function testCreatingASignupWithoutActivityScope()
    {
        $user = factory(User::class)->create();
        $campaignId = $this->faker->randomNumber(4);

        $scopes = ['user', 'write']; // no 'activity', so no go!
        $response = $this->asUser($user, $scopes)->postJson('api/v3/signups', [
            'campaign_id' => $campaignId,
            'details' => 'affiliate-messaging',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test that a POST request to /signups doesn't create duplicate signups.
     *
     * POST /api/v3/signups
     * @return void
     */
    public function testNotCreatingDuplicateSignups()
    {
        $signup = factory(Signup::class)->create();
        $owner = $signup->user;

        $response = $this->asUser($owner)->postJson('api/v3/signups', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'source' => 'the-fox-den',
            'details' => 'affiliate-messaging',
        ]);

        // Make sure we get the 200 response
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'campaign_id' => $signup->campaign_id,
                'quantity' => $signup->quantity,
            ],
        ]);
    }

    /**
     * Test that non-authenticated user's/apps can't post signups.
     *
     * @return void
     */
    public function testUnauthenticatedUserCreatingASignup()
    {
        $somebody = factory(User::class)->create();

        $response = $this->postJson('api/v3/signups', [
            'northstar_id' => $somebody->id,
            'campaign_id' => $this->faker->randomNumber(4),
            'source' => 'the-fox-den',
            'details' => 'affiliate-messaging',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test for retrieving all signups as non-admin and non-owner.
     * Non-admins/non-owners should not see why_participated, source, or details in response.
     *
     * GET /api/v3/signups
     * @return void
     */
    public function testSignupsIndexAsNonAdminNonOwner()
    {
        factory(Signup::class, 10)->create();

        $response = $this->getJson('api/v3/signups');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'northstar_id',
                    'campaign_id',
                    'quantity',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'cursor' => ['current', 'prev', 'next', 'count'],
            ],
        ]);
    }

    /**
     * Test for retrieving all signups as admin.
     * Admins should see why_participated, source, and details in response.
     *
     * GET /api/v3/signups
     * @return void
     */
    public function testSignupsIndexAsAdmin()
    {
        factory(Signup::class, 10)->create();

        $response = $this->asAdminUser()->getJson('api/v3/signups');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'northstar_id',
                    'campaign_id',
                    'quantity',
                    'created_at',
                    'updated_at',
                    'why_participated',
                    'source',
                    'details',
                ],
            ],
            'meta' => [
                'cursor' => ['current', 'prev', 'next', 'count'],
            ],
        ]);
    }

    /**
     * Test for retrieving all signups as owner.
     * Signup owner should see why_participated, source, and details in response.
     *
     * GET /api/v3/signups
     * @return void
     */
    public function testSignupsIndexAsOwner()
    {
        $owner = factory(User::class)->create();
        $signups = factory(Signup::class, 10)->create([
            'northstar_id' => $owner->id,
        ]);

        $response = $this->asUser($owner)->getJson('api/v3/signups');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'northstar_id',
                    'campaign_id',
                    'quantity',
                    'created_at',
                    'updated_at',
                    'why_participated',
                    'source',
                    'details',
                ],
            ],
            'meta' => [
                'cursor' => ['current', 'prev', 'next', 'count'],
            ],
        ]);
    }

    /**
     * Test for signup index with included pending post info. as non-admin/non-owner.
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups?include=posts
     * @return void
     */
    public function testSignupIndexWithIncludedPostsAsNonAdminNonOwner()
    {
        $post = factory(Post::class)->create();
        $signup = $post->signup;

        // Test with annoymous user that no posts are returned.
        $response = $this->getJson('api/v3/signups?include=posts');

        $response->assertOk();
        $response->assertJsonCount(0, 'data.0.posts.data');
    }

    /**
     * Test for signup index with included pending post info. as admin
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups?include=posts
     * @return void
     */
    public function testSignupIndexWithIncludedPostsAsAdmin()
    {
        $post = factory(Post::class)->create();

        // Test with admin that posts are returned.
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups?include=posts',
        );

        $response->assertOk();
        $response->assertJsonCount(1, 'data.0.posts.data');
    }

    /**
     * Test for signup index with included pending post info. as owner
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups?include=posts
     * @return void
     */
    public function testSignupIndexWithIncludedPostsAsOwner()
    {
        $post = factory(Post::class)->create();
        $owner = $post->user;

        // Test with owner that posts are returned.
        $response = $this->asUser($owner)->getJson(
            'api/v3/signups?include=posts',
        );

        $response->assertOk();
        $response->assertJsonCount(1, 'data.0.posts.data');
    }

    /**
     * Test for signup index with included post info. and include params as non-admin/non-owner.
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups?include=posts:type(text|photo)
     * @return void
     */
    public function testSignupIndexWithIncludedPostsAndParamsAsNonAdminNonOwner()
    {
        $signup = factory(Signup::class)->create();

        // Create a voter-reg post that is accepted.
        $post = factory(Post::class)->create();
        $post->type = 'voter-reg';
        $post->status = 'accepted';
        $post->signup()->associate($signup);
        $post->save();

        // Create a second photo post that is pending.
        $pendingPost = factory(Post::class)->create();
        $pendingPost->signup()->associate($signup);

        // Test with annoymous user that no posts are returned when using the include params.
        $response = $this->getJson(
            'api/v3/signups' . '?include=posts:type(text|photo)',
        );

        $response->assertOk();
        $response->assertJsonCount(0, 'data.0.posts.data');

        // Test with anoymous user that the voter reg post is returned since it is accepted.
        $response = $this->getJson('api/v3/signups' . '?include=posts');

        $response->assertOk();
        $response->assertJsonCount(1, 'data.0.posts.data');
    }

    /**
     * Test for signup index with included pending post info. and include params as admin
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups?include=posts:type(text|photo)
     * @return void
     */
    public function testSignupIndexWithIncludedPostsAndParamsAsAdmin()
    {
        $signup = factory(Signup::class)->create();

        // Create three voter registration posts, a text post, and a photo post.
        factory(Post::class, 3)->create([
            'type' => 'voter-reg',
            'signup_id' => $signup->id,
        ]);
        factory(Post::class)->create([
            'type' => 'text',
            'signup_id' => $signup->id,
        ]);
        factory(Post::class)->create([
            'type' => 'photo',
            'signup_id' => $signup->id,
        ]);

        // Test with admin that only photo and text posts are returned.
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups?include=posts:type(text|photo)',
        );

        $response->assertOk();
        $response->assertJsonCount(2, 'data.0.posts.data');

        // Test with admin that only voter-reg posts are returned.
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups?include=posts:type(voter-reg)',
        );

        $response->assertOk();
        $response->assertJsonCount(3, 'data.0.posts.data');
    }

    /**
     * Test for signup index with included pending post info. and include params as owner
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups?include=posts:type(text|photo)
     * @return void
     */
    public function testSignupIndexWithIncludedPostsAndParamsAsOwner()
    {
        $post = factory(Post::class)
            ->states('voter-reg')
            ->create();

        // Test with owner that voter reg post is returned.
        $response = $this->asUser($post->user)->getJson(
            'api/v3/signups?include=posts:type(voter-reg)',
        );

        $response->assertOk();
        $response->assertJsonCount(1, 'data.0.posts.data');

        // Test with owner that no posts are returned (because it does not match include params).
        $response = $this->asUser($post->user)->getJson(
            'api/v3/signups?include=posts:type(text|photo)',
        );

        $response->assertOk();
        $response->assertJsonCount(0, 'data.0.posts.data');
    }

    /**
     * Test for signup show with included pending post info. as non-admin/non-owner.
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups/:signup_id?include=posts
     * @return void
     */
    public function testSignupShowWithIncludedPostsAsNonAdminNonOwner()
    {
        $post = factory(Post::class)->create();
        $signup = $post->signup;

        // Test with annoymous user that no posts are returned.
        $response = $this->getJson(
            'api/v3/signups/' . $signup->id . '?include=posts',
        );

        $response->assertOk();
        $response->assertJsonCount(0, 'data.posts.data');
    }

    /**
     * Test for signup show with included pending post info. as admin
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups/:signup_id?include=posts
     * @return void
     */
    public function testSignupShowWithIncludedPostsAsAdmin()
    {
        $post = factory(Post::class)->create();
        $signup = $post->signup;

        // Test with admin that posts are returned.
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups/' . $signup->id . '?include=posts',
        );

        $response->assertOk();
        $response->assertJsonCount(1, 'data.posts.data');
        $response->assertJsonPath('data.posts.data.0.signup_id', $signup->id);
    }

    /**
     * Test for signup show with included pending post info. as owner
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups/:signup_id?include=posts
     * @return void
     */
    public function testSignupShowWithIncludedPostsAsOwner()
    {
        $post = factory(Post::class)->create();
        $signup = $post->signup;

        // Test with admin that posts are returned.
        $response = $this->asUser($post->user)->getJson(
            'api/v3/signups/' . $signup->id . '?include=posts',
        );
        $response->assertStatus(200);
        $decodedResponse = $response->json();

        $this->assertEquals(
            false,
            empty($decodedResponse['data']['posts']['data']),
        );
        $this->assertEquals(
            $signup->id,
            $decodedResponse['data']['posts']['data'][0]['signup_id'],
        );
    }

    /**
     * Test for signup show with included post info. and include params as non-admin/non-owner.
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups/:signup?include=posts:type(text|photo)
     * @return void
     */
    public function testSignupShowWithIncludedPostsAndParamsAsNonAdminNonOwner()
    {
        $signup = factory(Signup::class)->create();

        // Create a voter-reg post that is accepted.
        $post = factory(Post::class)
            ->states('voter-reg', 'accepted')
            ->create([
                'signup_id' => $signup->id,
            ]);

        // Create a second photo post that is pending.
        factory(Post::class)->create([
            'signup_id' => $signup->id,
        ]);

        // Test with annoymous user that no posts are returned when using the include params.
        $response = $this->getJson(
            'api/v3/signups/' . $signup->id . '?include=posts:type(text|photo)',
        );

        $response->assertOk();
        $response->assertJsonCount(0, 'data.posts.data');

        // Test with annoymous user that the voter reg post is returned since it is accepted.
        $response = $this->getJson(
            'api/v3/signups/' . $signup->id . '?include=posts',
        );

        $response->assertOk();
        $response->assertJsonCount(1, 'data.posts.data');
    }

    /**
     * Test for signup show with included pending post info. and include params as admin
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups/:signup?include=posts:type(text|photo)
     * @return void
     */
    public function testSignupShowWithIncludedPostsAndParamsAsAdmin()
    {
        $signup = factory(Signup::class)->create();

        // Create three voter registration posts, a text post, and a photo post.
        factory(Post::class, 3)->create([
            'type' => 'voter-reg',
            'signup_id' => $signup->id,
        ]);
        factory(Post::class)->create([
            'type' => 'text',
            'signup_id' => $signup->id,
        ]);
        factory(Post::class)->create([
            'type' => 'photo',
            'signup_id' => $signup->id,
        ]);

        // Test with admin that only photo and text posts are returned.
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups/' . $signup->id . '?include=posts:type(text|photo)',
        );

        $response->assertOk();
        $response->assertJsonCount(2, 'data.posts.data');

        // Test with admin that only voter-reg posts are returned.
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups/' . $signup->id . '?include=posts:type(voter-reg)',
        );

        $response->assertOk();
        $response->assertJsonCount(3, 'data.posts.data');
    }

    /**
     * Test for signup show with included pending post info. and include params as owner
     * Only admins/owners should be able to see pending/rejected posts.
     *
     * GET /api/v3/signups/:signup?include=posts:type(text|photo)
     * @return void
     */
    public function testSignupShowWithIncludedPostsAndParamsAsOwner()
    {
        // Create a voter reg post
        $post = factory(Post::class)
            ->states('voter-reg')
            ->create();

        $owner = $post->user;
        $signup = $post->signup;

        // Test with owner that voter reg post is returned.
        $response = $this->asUser($owner)->getJson(
            'api/v3/signups/' . $signup->id . '?include=posts:type(voter-reg)',
        );

        $response->assertOk();
        $response->assertJsonCount(1, 'data.posts.data');

        // Test with owner that no posts are returned (because it does not match include params).
        $response = $this->asUser($owner)->getJson(
            'api/v3/signups/' . $signup->id . '?include=posts:type(text|photo)',
        );

        $response->assertOk();
        $response->assertJsonCount(0, 'data.posts.data');
    }

    /**
     * Test for signup index with included user info. as admin.
     * Only admins/owners should be able to see all user info.
     *
     * GET /api/v3/signups?include=user
     * @return void
     */
    public function testSignupIndexWithIncludedUser()
    {
        $user = factory(User::class)->create([
            'addr_zip' => '10010',
        ]);

        factory(Signup::class)->create([
            'northstar_id' => $user->id,
        ]);

        // Test with admin that entire user is returned.
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups?include=user',
        );

        $response->assertOk();
        $response->assertJsonPath('data.0.user.data.addr_zip', '10010');
    }

    /**
     * Test for retrieving all signups as admin with northstar_id & campaign_id filters (and a combinations of all).
     *
     * GET /api/v3/signups?filter[northstar_id]=56d5baa7a59dbf106b8b45aa
     * GET /api/v3/signups?filter[campaign_id]=1
     * GET /api/v3/signups?filter[campaign_id]=1&filter[northstar_id]=56d5baa7a59dbf106b8b45aa
     * GET /api/v3/signups?filter[campaign_id]=1,2
     *
     * @return void
     */
    public function testSignupsIndexAsAdminWithFilters()
    {
        $firstUser = factory(User::class)->create();
        $campaignId = Str::random(22);

        // Create two signups
        factory(Signup::class, 2)->create([
            'northstar_id' => $firstUser->id,
            'campaign_id' => $campaignId,
        ]);

        // Create three more signups with different northstar_id & campaign_id
        $secondUser = factory(User::class)->create();
        $secondCampaignId = Str::random(22);

        factory(Signup::class, 3)->create([
            'northstar_id' => $secondUser->id,
            'campaign_id' => $secondCampaignId,
        ]);

        // Filter by northstar_id
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups?filter[northstar_id]=' . $firstUser->id,
        );
        $decodedResponse = $response->json();

        $response->assertStatus(200);

        // Assert only 2 signups are returned
        $this->assertEquals(2, $decodedResponse['meta']['cursor']['count']);

        // Filter by campaign_id
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups?filter[campaign_id]=' . $secondCampaignId,
        );
        $decodedResponse = $response->json();

        $response->assertStatus(200);

        // Assert only 3 signups are returned
        $this->assertEquals(3, $decodedResponse['meta']['cursor']['count']);

        // Filter by campaign_id and northstar_id
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups?filter[campaign_id]=' .
                $campaignId .
                '&filter[northstar_id]=' .
                $firstUser->id,
        );
        $decodedResponse = $response->json();

        $response->assertStatus(200);

        // Assert only 2 signups are returned
        $this->assertEquals(2, $decodedResponse['meta']['cursor']['count']);

        // Filter by multiple campaign_id
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups?filter[campaign_id]=' .
                $campaignId .
                ',' .
                $secondCampaignId,
        );
        $decodedResponse = $response->json();

        $response->assertStatus(200);

        // Assert all signups are returned
        $this->assertEquals(5, $decodedResponse['meta']['cursor']['count']);
    }

    /**
     * Test for retrieving a specific signup as non-admin and non-owner.
     *
     * GET /api/v3/signups/:signup_id
     * @return void
     */
    public function testSignupShowAsNonAdminNonOwner()
    {
        $signup = factory(Signup::class)->create();
        $response = $this->getJson('api/v3/signups/' . $signup->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'northstar_id',
                'campaign_id',
                'quantity',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Test for retrieving all signups & ordering them.
     *
     * GET /api/v3/signups?orderBy=quantity,desc
     * GET /api/v3/signups?orderBy=quantity,asc
     * @return void
     */
    public function testSignupsIndexAsAdminWithOrderBy()
    {
        $signup = factory(Signup::class)->create(); // quantity=6
        factory(Post::class, 3)->create([
            'signup_id' => $signup->id,
            'quantity' => 2,
        ]);

        $signup2 = factory(Signup::class)->create(); // quantity=12
        factory(Post::class, 4)->create([
            'signup_id' => $signup2->id,
            'quantity' => 3,
        ]);

        // Order results by descending quantity
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups?orderBy=quantity,desc',
        );

        $response->assertOk();
        $response->assertJsonPath('data.0.quantity', 12);
        $response->assertJsonPath('data.1.quantity', 6);

        // Order results by ascending quantity
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups?orderBy=quantity,asc',
        );

        $response->assertOk();
        $response->assertJsonPath('data.0.quantity', 6);
        $response->assertJsonPath('data.1.quantity', 12);
    }

    /**
     * Test for retrieving a specific signup as admin.
     *
     * GET /api/v3/signups/:signup_id
     * @return void
     */
    public function testSignupShowAsAdmin()
    {
        $signup = factory(Signup::class)->create();
        $response = $this->asAdminUser()->getJson(
            'api/v3/signups/' . $signup->id,
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'northstar_id',
                'campaign_id',
                'quantity',
                'created_at',
                'updated_at',
                'why_participated',
                'source',
                'details',
            ],
        ]);
    }

    /**
     * Test for retrieving a specific signup as owner.
     *
     * GET /api/v3/signups/:signup_id
     * @return void
     */
    public function testSignupShowAsOwner()
    {
        $signup = factory(Signup::class)->create();
        $owner = $signup->user;

        $response = $this->asUser($owner)->getJson(
            'api/v3/signups/' . $signup->id,
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'northstar_id',
                'campaign_id',
                'quantity',
                'created_at',
                'updated_at',
                'why_participated',
                'source',
                'details',
            ],
        ]);
    }

    /**
     * Test that a signup gets deleted when hitting the DELETE endpoint.
     *
     * @return void
     */
    public function testDeletingASignup()
    {
        $signup = factory(Signup::class)->create();

        // Mock time of when the signup is soft deleted.
        $this->mockTime('8/3/2017 14:00:00');

        $response = $this->asAdminUser()->deleteJson(
            'api/v3/signups/' . $signup->id,
        );

        $response->assertStatus(200);

        // Make sure that the signup's deleted_at gets persisted in the database.
        $this->assertEquals(
            $signup->fresh()->deleted_at->toTimeString(),
            '14:00:00',
        );
    }

    /**
     * Test that a signup cannot be deleted without activity scope.
     *
     * @return void
     */
    public function testDeletingASignupWithoutActivityScope()
    {
        $signup = factory(Signup::class)->create();

        $response = $this->deleteJson('api/v3/signups/' . $signup->id);

        $response->assertUnauthorized();
    }

    /**
     * Test that non-authenticated user's/apps can't delete signups.
     *
     * @return void
     */
    public function testUnauthenticatedUserDeletingASignup()
    {
        $signup = factory(Signup::class)->create();

        $response = $this->deleteJson('api/v3/signups/' . $signup->id);

        $response->assertStatus(401);
    }

    /**
     * Test for updating a signup successfully.
     *
     * PATCH /api/v3/signups/186
     * @return void
     */
    public function testUpdatingASignup()
    {
        $signup = factory(Signup::class)->create();

        $response = $this->asAdminUser()->patchJson(
            'api/v3/signups/' . $signup->id,
            [
                'why_participated' => 'new why participated',
            ],
        );

        $response->assertStatus(200);

        // Make sure that the signup's new why_participated gets persisted in the database.
        $this->assertEquals(
            $signup->fresh()->why_participated,
            'new why participated',
        );
    }

    /**
     * Test that a signup cannot be updated without the activity scope.
     *
     * PATCH /api/v3/signups/186
     * @return void
     */
    public function testUpdatingASignupWithoutActivityScope()
    {
        $signup = factory(Signup::class)->create();

        $response = $this->patchJson('api/v3/signups/' . $signup->id, [
            'why_participated' => 'new why participated',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test validation for updating a signup.
     *
     * PATCH /api/v3/signups/186
     * @return void
     */
    public function testValidationForUpdatingASignup()
    {
        $signup = factory(Signup::class)->create();

        $response = $this->asAdminUser()->patchJson(
            'api/v3/signups/' . $signup->id,
        );

        $response->assertStatus(422);
    }

    /**
     * Test that a non-admin or user that doesn't own the signup can't update signup.
     *
     * @return void
     */
    public function testUnauthenticatedUserUpdatingASignup()
    {
        $somebodyElse = factory(User::class)->create();

        $signup = factory(Signup::class)->create();

        $response = $this->asUser($somebodyElse)->patchJson(
            'api/v3/signups/' . $signup->id,
            [
                'why_participated' => 'new why participated',
            ],
        );

        $response->assertForbidden();
    }
}
