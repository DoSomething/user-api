<?php

namespace Tests\Http;

use App\Models\Action;
use App\Models\Campaign;
use App\Models\Club;
use App\Models\Group;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Signup;
use App\Models\User;
use App\Services\CustomerIo;
use App\Services\Fastly;
use App\Services\GraphQL;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PostTest extends TestCase
{
    /**
     * Helper function to assert post structure.
     */
    public function assertPostStructure($response)
    {
        return $response->assertJsonStructure([
            'data' => [
                'id',
                'signup_id',
                'northstar_id',
                'type',
                'action',
                'media' => ['url', 'original_image_url', 'text'],
                'quantity',
                'hours_spent',
                'tags' => [],
                'reactions' => ['reacted', 'total'],
                'status',
                'details',
                'location',
                'school_id',
                'club_id',
                'source',
                'remote_addr',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Test that a POST request to /posts creates a new
     * post and signup, if it doesn't already exist.
     *
     * @return void
     */
    public function testCreatingAPostAndSignup()
    {
        $user = factory(User::class)->create();
        $referrerUser = factory(User::class)->create();

        $campaignId = factory(Campaign::class)->create()->id;
        $quantity = $this->faker->numberBetween(10, 1000);
        $whyParticipated = $this->faker->paragraph;
        $text = $this->faker->sentence;
        $location = 'US-' . $this->faker->stateAbbr();
        $school_id = $this->faker->word;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $groupId = factory(Group::class)->create()->id;

        // Create an action to refer to.
        $action = factory(Action::class)->create([
            'campaign_id' => $campaignId,
        ]);

        // Create the post!
        $response = $this->asUser($user)->json('POST', 'api/v3/posts', [
            'campaign_id' => $campaignId,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'why_participated' => $whyParticipated,
            'text' => $text,
            'location' => $location,
            'school_id' => $school_id,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
            'details' => json_encode($details),
            'referrer_user_id' => $referrerUser->id,
            'group_id' => $groupId,
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        // Make sure the signup & post are persisted to the database.
        $this->assertMysqlDatabaseHas('signups', [
            'campaign_id' => $campaignId,
            'northstar_id' => $user->id,
            'why_participated' => $whyParticipated,
            'referrer_user_id' => $referrerUser->id,
            'group_id' => $groupId,
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'northstar_id' => $user->id,
            'campaign_id' => $campaignId,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'location' => $location,
            'school_id' => $school_id,
            'quantity' => $quantity,
            'details' => json_encode($details),
            'referrer_user_id' => $referrerUser->id,
            'group_id' => $groupId,
        ]);
    }

    /**
     * Test that a POST request to /posts creates a new
     * post and signup (if it doesn't already exist) without campaign_id.
     *
     * @return void
     */
    public function testCreatingAPostAndSignupWithoutCampaignId()
    {
        $user = factory(User::class)->create();
        $campaign = factory(Campaign::class)->create();

        $quantity = $this->faker->numberBetween(10, 1000);
        $whyParticipated = $this->faker->paragraph;
        $text = $this->faker->sentence;
        $location = 'US-' . $this->faker->stateAbbr();
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];

        // Create an action to refer to.
        $action = factory(Action::class)->create([
            'campaign_id' => $campaign->id,
        ]);

        // Create the post!
        $response = $this->asUser($user)->json('POST', 'api/v3/posts', [
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'why_participated' => $whyParticipated,
            'text' => $text,
            'location' => $location,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
            'details' => json_encode($details),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        // Make sure the signup & post are persisted to the database.
        $this->assertMysqlDatabaseHas('signups', [
            'campaign_id' => $campaign->id,
            'northstar_id' => $user->id,
            'why_participated' => $whyParticipated,
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'northstar_id' => $user->id,
            'campaign_id' => $campaign->id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'location' => $location,
            'quantity' => $quantity,
            'details' => json_encode($details),
        ]);
    }

    /**
     * Test that a POST request to /posts creates a new photo post.
     *
     * @return void
     */
    public function testCreatingAPhotoPost()
    {
        $signup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $hoursSpent = $this->faker->randomFloat(2, 0.1, 999999.99);
        $whyParticipated = $this->faker->paragraph;
        $text = $this->faker->sentence;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
        ]);

        // Create the post!
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'hours_spent' => $hoursSpent,
            'why_participated' => $whyParticipated,
            'text' => $text,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
            'details' => json_encode($details),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => $quantity,
            'hours_spent' => $hoursSpent,
            'details' => json_encode($details),
        ]);

        // Make sure the updated why_participated is updated on the signup.
        $this->assertMysqlDatabaseHas('signups', [
            'campaign_id' => $signup->campaign_id,
            'northstar_id' => $signup->northstar_id,
            'why_participated' => $whyParticipated,
        ]);
    }

    /**
     * Test that a POST request to /posts creates a new text post.
     *
     * @return void
     */
    public function testCreatingATextPost()
    {
        $signup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $whyParticipated = $this->faker->paragraph;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
            'post_type' => 'text',
        ]);

        // Create the post!
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'why_participated' => $whyParticipated,
            'text' => $text,
            'details' => json_encode($details),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => $quantity,
            'details' => json_encode($details),
        ]);

        // Make sure the updated why_participated is updated on the signup.
        $this->assertMysqlDatabaseHas('signups', [
            'campaign_id' => $signup->campaign_id,
            'northstar_id' => $signup->northstar_id,
            'why_participated' => $whyParticipated,
        ]);
    }

    /**
     * Test validation for creating a post.
     *
     * POST /api/v3/posts
     * @return void
     */
    public function testCreatingAPostWithValidationErrors()
    {
        $signup = factory(Signup::class)->create();

        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'campaign_id' => 'dog', // This should be a numeric ID.
            'signup_id' => $signup->id, // This one is okay.
            'school_id' => 234, // This should be a string.
            // and we've omitted the required 'type' and 'action' fields!
        ]);

        $response->assertJsonValidationErrors([
            'campaign_id',
            'type',
            'action',
            'school_id',
        ]);
    }

    /**
     * Test validation for creating a post with invalid file dimensions.
     *
     * POST /api/v3/posts
     * @return void
     */
    public function testCreatingAPostWithInvalidFileDimensions()
    {
        $signup = factory(Signup::class)->create();
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
        ]);

        $minImageSize = config('posts.image.min');
        $maxImageSize = config('posts.image.max');
        $validationMessage = "Photos must be no larger than 10MB, at least {$minImageSize['width']} x {$minImageSize['height']}, and no larger
          than {$maxImageSize['width']} x {$maxImageSize['height']}. Try cropping your photo.";

        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'type' => 'photo',
            'action_id' => $action->id,
            'file' => UploadedFile::fake()->image(
                'photo.jpg',
                $minImageSize['height'] - 1,
                $minImageSize['height'] - 1,
            ), // less than the minimum size!
        ]);

        $response->assertJsonValidationErrors(['file' => $validationMessage]);

        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'type' => 'photo',
            'action_id' => $action->id,
            'file' => UploadedFile::fake()->image(
                'photo.jpg',
                $maxImageSize['height'] + 1,
                $maxImageSize['height'] + 1,
            ), // more than the maximum size!
        ]);

        $response->assertJsonValidationErrors(['file' => $validationMessage]);
    }

    /**
     * Test validation for creating a post with invalid hours_spent.
     *
     * POST /api/v3/posts
     * @return void
     */
    public function testCreatingAPostWithInvalidHoursSpent()
    {
        $signup = factory(Signup::class)->create();
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
        ]);

        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'type' => 'photo',
            'action_id' => $action->id,
            'hours_spent' => 'one hundred', // This should be a number.
        ]);

        $response->assertJsonValidationErrors(['hours_spent']);

        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'type' => 'photo',
            'action_id' => $action->id,
            'hours_spent' => 1000000, // This is higher then our permitted maximum of 999999.99.
        ]);

        $response->assertJsonValidationErrors(['hours_spent']);

        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'type' => 'photo',
            'action_id' => $action->id,
            'hours_spent' => 0.0, // This should be a minimum of 0.01.
        ]);

        $response->assertJsonValidationErrors(['hours_spent']);
    }

    /**
     * We should silently discard bad location data.
     *
     * patch /api/v3/posts/195
     * @return void
     */
    public function testHandleBorkedLocationData()
    {
        $signup = factory(Signup::class)->create();
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
            'post_type' => 'text',
        ]);

        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'action_id' => $action->id,
            'type' => 'text',
            'text' => 'Lorem ipsum dolor sit amet.',
            'location' => 'Can\'t pin me down with your rules!!',
        ]);

        // We should save the post, but discard the bad location:
        $response->assertSuccessful();
        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'type' => 'text',
            'text' => 'Lorem ipsum dolor sit amet.',
            'location' => null,
        ]);
    }

    /**
     * Test that a POST request to /posts creates a new share-social post.
     *
     * @return void
     */
    public function testCreatingAShareSocialPost()
    {
        $signup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
            'post_type' => 'share-social',
        ]);

        // Create the post!
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'text' => $text,
            'details' => json_encode($details),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            // Social share posts should be auto-accepted (unless an admin sends a custom status).
            'status' => 'accepted',
            'details' => json_encode($details),
        ]);
    }

    /**
     * Test that a POST request to /posts as an admin creates a new auto-accepted share-social post.
     *
     * @return void
     */
    public function testCreatingAShareSocialPostAsAdmin()
    {
        $signup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
            'post_type' => 'share-social',
        ]);

        // Create the post!
        $response = $this->asStaffUser()->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => 'share-social',
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'text' => $text,
            'details' => json_encode($details),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            // Social share posts should be auto-accepted (unless an admin sends a custom status).
            'status' => 'accepted',
            'details' => json_encode($details),
        ]);
    }

    /**
     * Test that a POST request to /posts as an admin with a custom status creates a new share-social post that is pending.
     *
     * @return void
     */
    public function testCreatingAShareSocialPostAsAdminWithCustomStatus()
    {
        $signup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
            'post_type' => 'share-social',
        ]);

        // Create the post!
        $response = $this->asStaffUser()->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'text' => $text,
            'details' => json_encode($details),
            'status' => 'pending',
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            // Social share posts should be pending since the admin sent a custom status.
            'status' => 'pending',
            'details' => json_encode($details),
        ]);
    }

    /**
     * Test a post cannot be created that is not one of the following types: text, photo, voter-reg, share-social.
     *
     * @return void
     */
    public function testCreatingAPostWithoutValidTypeScopes()
    {
        $signup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];

        // Create the post with an invalid type (not in text, photo, voter-reg, share-social).
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => 'social-share',
            'action' => 'test-action',
            'quantity' => $quantity,
            'text' => $text,
            'details' => json_encode($details),
        ]);

        $response->assertStatus(422);
        $this->assertEquals(
            'The selected type is invalid.',
            $response->decodeResponseJson()['errors']['type'][0],
        );
    }

    /**
     * Test a post cannot be created without the activity & write scope.
     *
     * @return void
     */
    public function testCreatingAPostWithoutRequiredScopes()
    {
        $signup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;

        // Make sure you also need the activity scope.
        $response = $this->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => 'photo',
            'action' => 'test-action',
            'quantity' => $quantity,
            'why_participated' => $this->faker->paragraph,
            'text' => $text,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test that a POST request to /posts with an existing post creates an additional new photo post.
     *
     * @return void
     */
    public function testCreatingMultiplePosts()
    {
        $signup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
        ]);

        // Create the post!
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'why_participated' => $this->faker->paragraph,
            'text' => $text,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
            'details' => json_encode($details),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => $quantity,
            'details' => json_encode($details),
        ]);

        // Create a second post without why_participated.
        $secondQuantity = $this->faker->numberBetween(10, 1000);
        $secondText = $this->faker->sentence;
        $secondDetails = [
            'source-detail' => 'broadcast-333',
            'other' => 'other',
        ];

        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $secondQuantity,
            'text' => $secondText,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
            'details' => json_encode($secondDetails),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => $secondQuantity,
            'details' => json_encode($secondDetails),
        ]);

        // Assert that signup quantity is sum of all posts' quantities.
        $this->assertEquals(
            $signup->fresh()->quantity,
            $quantity + $secondQuantity,
        );
    }

    /**
     * Test that a POST request to /posts with `null` as the quantity creates a new post.
     *
     * @return void
     */
    public function testCreatingAPostWithNullAsQuantity()
    {
        $signup = factory(Signup::class)->create();
        $text = $this->faker->sentence;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
        ]);

        // Create the post!
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => null,
            'text' => $text,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
            'details' => json_encode($details),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => null,
            'details' => json_encode($details),
        ]);
    }

    /**
     * Test that a POST request to /posts without a quantity param creates a new post.
     *
     * @return void
     */
    public function testCreatingAPostWithoutQuantityParam()
    {
        $signup = factory(Signup::class)->create();
        $text = $this->faker->sentence;
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
        ]);

        // Create the post!
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'text' => $text,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => null,
            'details' => null,
        ]);
    }

    /**
     * Test that non-authenticated user's/apps can't create a post.
     *
     * @return void
     */
    public function testUnauthenticatedUserCreatingAPost()
    {
        $signup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;

        // Create the post!
        $response = $this->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => 'photo',
            'action' => 'test-action',
            'quantity' => $quantity,
            'why_participated' => $this->faker->paragraph,
            'text' => $text,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test creating a post without sending an action_id.
     *
     * @return void
     */
    public function testCreatingAPostWithoutSendingActionId()
    {
        $signup = factory(Signup::class)->create();
        $text = $this->faker->sentence;
        $quantity = $this->faker->numberBetween(10, 1000);
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
            'name' => 'test-action',
        ]);

        // Create the post without sending an action_id!
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => 'test-action',
            'quantity' => $quantity,
            'why_participated' => $this->faker->paragraph,
            'text' => $text,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => 'test-action',
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => $quantity,
        ]);
    }

    /**
     * Test for retrieving all posts as non-admin and non-owner.
     * Non-admins/non-owners should not see tags, source, and remote_addr.
     *
     * GET /api/v3/posts
     * @return void
     */
    public function testPostsIndexAsNonAdminNonOwner()
    {
        // Anonymous requests should only see accepted posts.
        factory(Post::class, 10)
            ->states('photo', 'accepted')
            ->create();
        factory(Post::class, 5)
            ->states('photo', 'rejected')
            ->create();

        $response = $this->getJson('api/v3/posts');

        $response->assertOk();
        $response->assertJsonCount(10, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'signup_id',
                    'northstar_id',
                    'media' => ['url', 'original_image_url', 'text'],
                    'quantity',
                    'reactions' => ['reacted', 'total'],
                    'status',
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
     * Posts can be filtered by user ID.
     *
     * @test
     */
    public function testFilteringPostsIndexByUserId()
    {
        $user = factory(User::class)->create();

        factory(Post::class, 2)
            ->states('accepted')
            ->create([
                'northstar_id' => $user->id,
            ]);

        factory(Post::class, 3)
            ->states('accepted')
            ->create();

        $response = $this->getJson(
            "api/v3/posts?filter%5Bnorthstar_id%5D={$user->id}",
        );

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Guests shouldn't see user IDs on anonymous actions.
     *
     * @test
     */
    public function testPostsIndexWithAnonymousPostsAsGuest()
    {
        factory(Post::class)
            ->states('anonymous', 'accepted')
            ->create();

        $response = $this->getJson('api/v3/posts');

        $response->assertOk();
        $response->assertJsonPath('data.0.northstar_id', null);
    }

    /**
     * Non-owners shouldn't see user IDs on anonymous actions.
     *
     * @test
     */
    public function testPostsIndexWithAnonymousPostsAsSomebodyElse()
    {
        $owner = factory(User::class)->create();
        $somebodyElse = factory(User::class)->create();

        factory(Post::class)
            ->states('anonymous', 'accepted')
            ->create(['northstar_id' => $owner->id]);

        $response = $this->asUser($somebodyElse)->getJson('api/v3/posts');

        $response->assertOk();
        $response->assertJsonPath('data.0.northstar_id', null);
    }

    /**
     * Guests shouldn't see anonymous posts when filtering by ID.
     *
     * @test
     */
    public function testFilteringPostsIndexWithAnonymousPostsAsGuest()
    {
        $owner = factory(User::class)->create();

        factory(Post::class)
            ->states('anonymous', 'accepted')
            ->create(['northstar_id' => $owner->id]);

        $response = $this->getJson(
            'api/v3/posts?filter[northstar_id]=' . $owner->id,
        );

        $response->assertOk();
        $response->assertJsonPath('meta.cursor.count', 0);
    }

    /**
     * Non-owners shouldn't see anonymous posts when filtering by ID.
     *
     * @test
     */
    public function testFilteringPostsIndexWithAnonymousPostsAsSomebodyElse()
    {
        $owner = factory(User::class)->create();
        $somebodyElse = factory(User::class)->create();

        factory(Post::class)
            ->states('anonymous', 'accepted')
            ->create(['northstar_id' => $owner->id]);

        $response = $this->asUser($somebodyElse)->getJson(
            'api/v3/posts?filter[northstar_id]=' . $owner->id,
        );

        $response->assertOk();
        $response->assertJsonPath('meta.cursor.count', 0);
    }

    /**
     * Owners should see their anonymous posts on index.
     *
     * @test
     */
    public function testPostsIndexWithAnonymousPostsAsOwner()
    {
        $owner = factory(User::class)->create();

        $post = factory(Post::class)
            ->states('anonymous', 'accepted')
            ->create(['northstar_id' => $owner->id]);

        $response = $this->asUser($owner)->getJson('api/v3/posts');

        $response->assertOk();
        $response->assertJsonPath('data.0.northstar_id', $post->northstar_id);
    }

    /**
     * Owners should be able to filter & see their anonymous posts.
     *
     * @test
     */
    public function testFilteringPostsIndexWithAnonymousPostsAsOwner()
    {
        $owner = factory(User::class)->create();

        $post = factory(Post::class)
            ->states('anonymous', 'accepted')
            ->create(['northstar_id' => $owner->id]);

        $response = $this->asUser($owner)->getJson(
            'api/v3/posts?filter[northstar_id]=' . $owner->id,
        );

        $response->assertOk();
        $response->assertJsonPath('data.0.northstar_id', $post->northstar_id);
    }

    /**
     * Staff should see anonymous posts on index.
     *
     * @test
     */
    public function testPostsIndexWithAnonymousPostsAsStaff()
    {
        $owner = factory(User::class)->create();

        $post = factory(Post::class)
            ->states('anonymous', 'accepted')
            ->create(['northstar_id' => $owner->id]);

        $response = $this->asStaffUser()->getJson('api/v3/posts');

        $response->assertOk();
        $response->assertJsonPath('data.0.northstar_id', $post->northstar_id);
    }

    /**
     * Staff should see anonymous posts when filtering by user ID.
     *
     * @test
     */
    public function testFilteringPostsIndexWithAnonymousPostsAsStaff()
    {
        $owner = factory(User::class)->create();

        $post = factory(Post::class)
            ->states('anonymous', 'accepted')
            ->create(['northstar_id' => $owner->id]);

        $response = $this->asStaffUser()->getJson(
            'api/v3/posts?filter[northstar_id]=' . $post->northstar_id,
        );

        $response->assertOk();
        $response->assertJsonPath('data.0.northstar_id', $post->northstar_id);
    }

    /**
     * Test for retrieving all posts as non-admin and non-owner.
     * Posts tagged as "Hide In Gallery" should not be returned to Non-admins/non-owners.
     *
     * GET /api/v3/posts
     * @return void
     */
    public function testPostsIndexAsNonAdminNonOwnerHiddenPosts()
    {
        // Anonymous requests should only see posts that are not tagged with "Hide In Gallery."
        factory(Post::class, 10)
            ->states('photo', 'accepted')
            ->create();

        $hiddenPost = factory(Post::class)
            ->states('photo', 'accepted')
            ->create();
        $hiddenPost->tag('Hide In Gallery');

        $response = $this->getJson('api/v3/posts');

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
    }

    /**
     * Test for retrieving all posts as admin
     * Admins should see tags, source, and remote_addr.
     *
     * GET /api/v3/posts
     * @return void
     */
    public function testPostsIndexAsAdmin()
    {
        // Admins should see all posts.
        factory(Post::class, 10)
            ->states('photo', 'accepted')
            ->create();
        factory(Post::class, 5)
            ->states('photo', 'rejected')
            ->create();

        $response = $this->asStaffUser()->getJson('api/v3/posts');

        $response->assertOk();
        $response->assertJsonCount(15, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'signup_id',
                    'northstar_id',
                    'type',
                    'action',
                    'media' => ['url', 'original_image_url', 'text'],
                    'quantity',
                    'reactions' => ['reacted', 'total'],
                    'status',
                    'created_at',
                    'updated_at',
                    'tags' => [],
                    'source',
                    'details',
                    'remote_addr',
                ],
            ],
            'meta' => [
                'cursor' => ['current', 'prev', 'next', 'count'],
            ],
        ]);
    }

    /**
     * Test for retrieving all posts as admin
     * Admins should see hidden posts.
     *
     * GET /api/v3/posts
     * @return void
     */
    public function testPostsIndexAsAdminHiddenPosts()
    {
        // Admins should see all posts.
        factory(Post::class, 10)
            ->states('photo', 'accepted')
            ->create();

        $hiddenPost = factory(Post::class)
            ->states('photo', 'accepted')
            ->create();
        $hiddenPost->tag('Hide In Gallery');

        $response = $this->asStaffUser()->getJson('api/v3/posts');
        $response->assertOk();
        $response->assertJsonCount(11, 'data');
    }

    /**
     * Test for retrieving posts as admin filtered by status.
     *
     * GET /api/v3/posts
     * @return void
     */
    public function testPostsIndexFilteredByStatusAsAdmin()
    {
        factory(Post::class, 1)
            ->states('photo', 'accepted')
            ->create();
        factory(Post::class, 5)
            ->states('photo', 'pending')
            ->create();
        factory(Post::class, 7)
            ->states('photo', 'rejected')
            ->create();

        // Admins should see posts filtered by pending status.
        $response = $this->asStaffUser()->getJson(
            'api/v3/posts?filter[status]=pending',
        );

        $response->assertOk();
        $response->assertJsonCount(5, 'data');

        // Admins should be able to filter by multiple statuses and see pending and rejected posts.
        $response = $this->asStaffUser()->getJson(
            'api/v3/posts?filter[status]=pending,rejected',
        );

        $response->assertOk();
        $response->assertJsonCount(12, 'data');
    }

    /**
     * Test for retrieving posts filtered by the volunteer_credit value of the associated action.
     *
     * GET /api/v3/posts
     * @return void
     */
    public function testPostsIndexFilteredByVolunteerCredit()
    {
        $action = factory(Action::class)->create([
            'volunteer_credit' => true,
        ]);
        // Posts qualifying for volunteer credit:
        factory(Post::class, 4)
            ->states('photo', 'accepted')
            ->create([
                'action_id' => $action->id,
            ]);
        // Posts not qualifying for volunteer credit:
        factory(Post::class, 7)
            ->states('photo', 'accepted')
            ->create();

        $response = $this->getJson(
            'api/v3/posts?filter[volunteer_credit]=true',
        );
        $response->assertSuccessful();
        $response->assertJsonCount(4, 'data');

        $response = $this->getJson(
            'api/v3/posts?filter[volunteer_credit]=false',
        );
        $response->assertSuccessful();
        $response->assertJsonCount(7, 'data');
    }

    /**
     * Test for retrieving all posts as owner.
     * Owners should see tags, source, and remote_addr.
     *
     * GET /api/v3/posts
     * @return void
     */
    public function testPostsIndexAsOwner()
    {
        $user = factory(User::class)->create();

        factory(Post::class, 2)
            ->states('photo', 'pending')
            ->create(['northstar_id' => $user->id]);

        factory(Post::class)
            ->states('photo', 'rejected')
            ->create(['northstar_id' => $user->id]);

        $somebodyElse = factory(User::class)->create();
        factory(Post::class, 4)
            ->states('photo', 'rejected')
            ->create(['northstar_id' => $somebodyElse->id]);

        // Owners should be able to see their own posts of any status, but
        // not pending or rejected posts from other users.
        $response = $this->asUser($user)->getJson('api/v3/posts');

        $response->assertSuccessful();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'signup_id',
                    'northstar_id',
                    'type',
                    'action',
                    'media' => ['url', 'original_image_url', 'text'],
                    'quantity',
                    'reactions' => ['reacted', 'total'],
                    'status',
                    'created_at',
                    'updated_at',
                    'tags' => [],
                    'source',
                    'details',
                    'remote_addr',
                ],
            ],
            'meta' => [
                'cursor' => ['current', 'prev', 'next', 'count'],
            ],
        ]);
    }

    /**
     * Test for retrieving all posts as owner.
     * Owners should see their own hidden posts.
     *
     * GET /api/v3/posts
     * @return void
     */
    public function testPostsIndexAsOwnerHiddenPosts()
    {
        // Owners should only see accepted posts and their own hidden posts.
        $owner = factory(User::class)->create();

        // Create posts and associate to this $ownerId.
        $posts = factory(Post::class, 2)
            ->states('photo', 'accepted')
            ->create(['northstar_id' => $owner->id]);

        // Create a hidden post from the same $ownerId.
        $hiddenPost = factory(Post::class)
            ->states('photo', 'accepted')
            ->create(['northstar_id' => $owner->id]);
        $hiddenPost->tag('Hide In Gallery');
        $hiddenPost->save();

        // New Owner of the second hidden post
        $hiddenOwner = factory(User::class)->create();

        // Create another hidden post by different user.
        $secondHiddenPost = factory(Post::class)
            ->states('photo', 'accepted')
            ->create([
                'northstar_id' => $hiddenOwner->id,
            ]);
        $secondHiddenPost->tag('Hide In Gallery');
        $secondHiddenPost->save();

        $response = $this->asUser($owner)->getJson('api/v3/posts');
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Test for retrieving voter registration posts as a referrer.
     * A referrer should see any voter registration post that they have referred.
     *
     * GET /api/v3/posts
     * @return void
     */
    public function testPostsIndexAsVoterRegistrationReferrer()
    {
        $referrerUser = factory(User::class)->create();

        // A referrer can see all of their voter registration referrals.
        $firstVoterRegReferral = factory(Post::class)
            ->states('voter-reg', 'register-form')
            ->create(['referrer_user_id' => $referrerUser->id]);
        $secondVoterRegReferral = factory(Post::class)
            ->states('voter-reg', 'rejected')
            ->create(['referrer_user_id' => $referrerUser->id]);
        $thirdVoterRegReferral = factory(Post::class)
            ->states('voter-reg', 'step-1')
            ->create(['referrer_user_id' => $referrerUser->id]);

        // Add a completed voter reg without a referrer, which is public because it's completed.
        $publicVoterRegPost = factory(Post::class)
            ->states('voter-reg', 'register-OVR')
            ->create();

        // Add non-completed voter referrals for a different referrer, which shouldn't be visible.
        factory(Post::class)
            ->states('voter-reg', 'step-1')
            ->create([
                'referrer_user_id' => $this->faker->unique()->northstar_id,
            ]);

        // Add a pending photo post, which shouldn't be visible.
        factory(Post::class)
            ->states('photo', 'pending')
            ->create();

        $response = $this->asUser($referrerUser)->getJson('api/v3/posts');
        $response->assertOk();
        $response->assertJsonCount(4, 'data');
        $response->assertJsonFragment([
            'id' => $firstVoterRegReferral->id,
            'status' => 'register-form',
        ]);
        $response->assertJsonFragment([
            'id' => $secondVoterRegReferral->id,
            'status' => 'rejected',
        ]);
        $response->assertJsonFragment([
            'id' => $thirdVoterRegReferral->id,
            'status' => 'step-1',
        ]);
        $response->assertJsonFragment([
            'id' => $publicVoterRegPost->id,
            'status' => 'register-OVR',
        ]);
    }

    /**
     * Test for retrieving a specific post as non-admin and non-owner.
     * Non-admin and non-owners can't see other's unapproved or any rejected posts.
     *
     * GET /api/v3/post/:post_id
     * @return void
     */
    public function testPostShowAsNonAdminNonOwner()
    {
        // Anon user should not be able to see a pending post if it doesn't belong to them and if they're not an admin.
        $post = factory(Post::class)->create();

        $response = $this->getJson('api/v3/posts/' . $post->id);

        $response->assertForbidden();

        // Anon user should not be able to see a rejected post if it doesn't belong to them and if they're not an admin.
        $post = factory(Post::class)
            ->states('photo', 'rejected')
            ->create();

        $response = $this->getJson('api/v3/posts/' . $post->id);

        $response->assertForbidden();

        // Anon user is able to see an accepted post even if it doesn't belong to them and if they're not an admin.
        $post = factory(Post::class)
            ->states('photo', 'accepted')
            ->create();

        $response = $this->getJson('api/v3/posts/' . $post->id);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'signup_id',
                'northstar_id',
                'type',
                'action',
                'media' => ['url', 'original_image_url', 'text'],
                'quantity',
                'reactions' => ['reacted', 'total'],
                'status',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Test for retrieving a specific post as admin.
     * Admins can see all posts (unapproved, rejected, and post that don't belong to them).
     *
     * GET /api/v3/post/:post_id
     * @return void
     */
    public function testPostShowAsAdmin()
    {
        $post = factory(Post::class)->create();

        $response = $this->asStaffUser()->getJson('api/v3/posts/' . $post->id);

        $response->assertOk();
        $response->assertJsonPath('data.id', $post->id);

        $this->assertPostStructure($response);
    }

    /**
     * Test for retrieving a specific post as owner (and only owners can see their own unapproved posts).
     *
     * GET /api/v3/post/:post_id
     * @return void
     */
    public function testPostShowAsOwner()
    {
        $post = factory(Post::class)->create();
        $response = $this->asUser($post->user)->getJson(
            'api/v3/posts/' . $post->id,
        );

        $response->assertOk();
        // $this->assertPostStructure($response);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'signup_id',
                'northstar_id',
                'type',
                'action',
                'media' => ['url', 'original_image_url', 'text'],
                'quantity',
                'reactions' => ['reacted', 'total'],
                'status',
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertJsonPath('data.id', $post->id);
    }

    /**
     * Test for retrieving a specific post with reactions.
     *
     * GET /api/v3/post/:post_id
     * @return void
     */
    public function testPostShowWithReactions()
    {
        $viewer = factory(User::class)->create();

        $post = factory(Post::class)
            ->states('photo', 'accepted')
            ->create();

        // Create two reactions for this post!
        Reaction::withTrashed()->firstOrCreate([
            'northstar_id' => $viewer->id,
            'post_id' => $post->id,
        ]);
        Reaction::withTrashed()->firstOrCreate([
            'northstar_id' => 'someone_else_lol',
            'post_id' => $post->id,
        ]);

        $response = $this->asUser($viewer)->getJson(
            'api/v3/posts/' . $post->id,
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id' => $post->id,
                'reactions' => [
                    'reacted' => true,
                    'total' => 2,
                ],
            ],
        ]);
    }

    /**
     * Test for updating a post successfully.
     *
     * PATCH /api/v3/posts/:id
     * @return void
     */
    public function testUpdatingAPhotoPost()
    {
        $post = factory(Post::class)->create();
        $signup = $post->signup;

        $response = $this->asStaffUser()->patchJson(
            'api/v3/posts/' . $post->id,
            [
                'text' => 'new caption',
                'quantity' => 8,
                'status' => 'accepted',
                'school_id' => '200426',
            ],
        );

        $response->assertOk();

        // Make sure that the post's new status, text, and school_id gets persisted in the database.
        $this->assertEquals($post->fresh()->text, 'new caption');
        $this->assertEquals($post->fresh()->quantity, 8);
        $this->assertEquals($post->fresh()->school_id, '200426');

        // Make sure the signup's quantity gets updated.
        $this->assertEquals($signup->fresh()->quantity, 8);
    }

    /**
     * Test that updating a referral post sends an event to Customer.io.
     *
     * PATCH /api/v3/posts/:id
     * @return void
     */
    public function testUpdatingAReferralPost()
    {
        $referrer = factory(User::class)->create();

        $post = factory(Post::class)->create([
            'referrer_user_id' => $referrer->id,
            'type' => 'voter-reg',
            'status' => 'step-1',
        ]);

        // $this->spy(\App\Services\CustomerIo::class);

        $this->asStaffUser()->patchJson('api/v3/posts/' . $post->id, [
            'status' => 'register-form',
        ]);

        $this->assertEquals('register-form', $post->fresh()->status);
        $this->assertCustomerIoEvent($referrer, 'referral_post_updated');
    }

    /**
     * Test for updating a post with invalid status.
     *
     * PATCH /api/v3/posts/:id
     * @return void
     */
    public function testUpdatingAPhotoWithInvalidStatus()
    {
        $post = factory(Post::class)->create();

        $response = $this->asStaffUser()->patchJson(
            'api/v3/posts/' . $post->id,
            [
                'text' => 'new caption',
                'quantity' => 8,
                'status' => 'register-form',
            ],
        );

        $response->assertJsonValidationErrors(['status']);
    }

    /**
     * Test for updating a post with invalid school_id.
     *
     * PATCH /api/v3/posts/:id
     * @return void
     */
    public function testUpdatingAPhotoWithInvalidSchoolId()
    {
        $post = factory(Post::class)->create();

        $response = $this->asStaffUser()->patchJson(
            'api/v3/posts/' . $post->id,
            [
                'school_id' => 8,
            ],
        );

        $response->assertJsonValidationErrors(['school_id']);
    }

    /**
     * Test for updating a post without activity scope.
     *
     * PATCH /api/v3/posts/:id
     * @return void
     */
    public function testUpdatingAPostWithoutActivityScope()
    {
        $post = factory(Post::class)->create();
        $signup = $post->signup;

        $response = $this->patchJson('api/v3/posts/' . $post->id, [
            'text' => 'new caption',
            'quantity' => 8,
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test validation for updating a post.
     *
     * PATCH /api/v3/posts/:id
     * @return void
     */
    public function testValidationUpdatingAPost()
    {
        $post = factory(Post::class)->create();

        $response = $this->asStaffUser()->patchJson(
            'api/v3/posts/' . $post->id,
            [
                'quantity' => 'this is words not a number!',
                'text' => 'a' . str_repeat('h', 512), // ahhh...hhhhh!
            ],
        );

        $response->assertJsonValidationErrors(['quantity', 'text']);
    }

    /**
     * Test that a user can update their own post, but can't
     * change its review status.
     *
     * @return void
     */
    public function testNonStaffUpdatePost()
    {
        $post = factory(Post::class)->create();

        $response = $this->asUser($post->user)->patchJson(
            'api/v3/posts/' . $post->id,
            [
                'status' => 'accepted',
                'location' => 'US-MA',
                'text' => 'new caption',
            ],
        );

        $response->assertOk();

        $response->assertJson([
            'data' => [
                'media' => [
                    'text' => 'new caption', // check!
                ],
                'location' => 'US-MA', // check!
                'status' => 'pending', // no way, buddy!
            ],
        ]);
    }

    /**
     * Test that a non-admin or user that doesn't own the post can't update post.
     *
     * @return void
     */
    public function testUnauthorizedUserUpdatingPost()
    {
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        $response = $this->asUser($user)->patchJson(
            'api/v3/posts/' . $post->id,
            [
                'status' => 'accepted',
                'text' => 'new caption',
            ],
        );

        $response->assertForbidden();
    }

    /**
     * Test that a post gets deleted when hitting the DELETE endpoint.
     *
     * @return void
     */
    public function testDeletingAPost()
    {
        $post = factory(Post::class)->create();

        // Mock time of when the post is soft deleted.
        $this->mockTime('8/3/2017 14:00:00');

        // Mock the Fastly API calls.
        $this->mock(Fastly::class)->shouldReceive('purge');

        $response = $this->asStaffUser()->deleteJson(
            'api/v3/posts/' . $post->id,
        );

        $response->assertOk();

        // Make sure that the post's deleted_at gets persisted in the database.
        $this->assertEquals(
            $post->fresh()->deleted_at->toTimeString(),
            '14:00:00',
        );
    }

    /**
     * Test that non-authenticated user's/apps can't delete posts.
     *
     * @return void
     */
    public function testUnauthenticatedUserDeletingAPost()
    {
        $post = factory(Post::class)->create();

        $response = $this->deleteJson('api/v3/posts/' . $post->id);

        $response->assertUnauthorized();
    }

    /**
     * Test creating voter-reg post.
     *
     * @return void
     */
    public function testCreatingVoterRegistrationPost()
    {
        $signup = factory(Signup::class)->create();
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
            'post_type' => 'voter-reg',
        ]);

        $details = [
            'hostname' => 'dosomething.turbovote.org',
            'referral-code' =>
                'user:5570af2c469c6430068bc501,campaign:8022,source:web',
            'partner-comms-opt-in' => '',
            'created-at' => '2018-01-29T01:59:44Z',
            'updated-at' => '2018-01-29T02:00:17Z',
            'voter-registration-status' => 'initiated',
            'voter-registration-source' => 'turbovote',
            'voter-registration-method' => 'by-mail',
            'voting-method-preference' => 'in-person',
            'email subscribed' => 'FALSE',
            'sms subscribed' => 'TRUE',
        ];

        // Create the post!
        $response = $this->asStaffUser()->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'register-form',
            'details' => json_encode($details),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'register-form',
            'details' => json_encode($details),
        ]);
    }

    /**
     * Test that when a post is created for a signup with a group_id
     * the group_id is saved to the post as well.
     *
     * @return void
     */
    public function testCreatingAPostForSignupWithGroupId()
    {
        $groupId = factory(Group::class)->create()->id;
        $signup = factory(Signup::class)->create(['group_id' => $groupId]);

        // Attributes for the post that we'll create
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
        ]);

        // Create the post!
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'text' => $text,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'group_id' => $signup->group_id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => $quantity,
        ]);
    }

    /**
     * Test that when a post is created for a signup with a club_id
     * the club_id is saved to the post as well.
     *
     * @return void
     */
    public function testCreatingAPostForSignupWithClubId()
    {
        // Turn on the feature flag for tracking club_ids.
        config(['features.track_club_id' => 'true']);

        $clubId = factory(Club::class)->create()->id;
        $signup = factory(Signup::class)->create(['club_id' => $clubId]);

        // Attributes for the post that we'll create
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
        ]);

        // Create the post!
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'text' => $text,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'signup_id' => $signup->id,
            'club_id' => $signup->club_id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => $quantity,
        ]);
    }

    /**
     * Test that when a post with a group_id is created and no signup exists yet
     * the group_id on the post is not overwritten.
     *
     * @return void
     */
    public function testCreatingAPostWithGroupIdAndNoExistingSignup()
    {
        $user = factory(User::class)->create();
        $group = factory(Group::class)->create();

        // Attributes for the post that we'll create
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $campaign_id = factory(Campaign::class)->create()->id;
        $action = factory(Action::class)->create([
            'campaign_id' => $campaign_id,
        ]);

        // Create the post!
        $response = $this->asUser($user)->postJson('api/v3/posts', [
            'northstar_id' => $user->id,
            'campaign_id' => $campaign_id,
            'type' => $action->post_type,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'text' => $text,
            'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
            'group_id' => $group->id,
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $this->assertMysqlDatabaseHas('posts', [
            'group_id' => $group->id,
            'northstar_id' => $user->id,
            'campaign_id' => $campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'status' => 'pending',
            'quantity' => $quantity,
        ]);
    }

    /**
     * Test that after a text post has been successfully created, a "one_post" badge is added to the user.
     *
     * @return void
     */
    public function testATextPostAddingABadge()
    {
        $signup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $whyParticipated = $this->faker->paragraph;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $signup->campaign_id,
            'post_type' => 'text',
        ]);

        // Create the post!
        $response = $this->asUser($signup->user)->postJson('api/v3/posts', [
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'why_participated' => $whyParticipated,
            'text' => $text,
            'details' => json_encode($details),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $user = $signup->user->fresh();
        $this->assertEquals(['signup', 'one-post'], $user->badges);
    }

    /**
     * Test that after multiple posts have been successfully created, "one_post", "two_posts", and "three_posts" badges are added to the user.
     *
     * @return void
     */
    public function testMultiplePostsAddingABadges()
    {
        $photoSignup = factory(Signup::class)->create();
        $quantity = $this->faker->numberBetween(10, 1000);
        $hoursSpent = $this->faker->randomFloat(2, 0.1, 999999.99);
        $whyParticipated = $this->faker->paragraph;
        $text = $this->faker->sentence;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $photoSignup->campaign_id,
        ]);

        // Create the post!
        $response = $this->asUser($photoSignup->user)->postJson(
            'api/v3/posts',
            [
                'northstar_id' => $photoSignup->northstar_id,
                'campaign_id' => $photoSignup->campaign_id,
                'type' => $action->post_type,
                'action' => $action->name,
                'action_id' => $action->id,
                'quantity' => $quantity,
                'hours_spent' => $hoursSpent,
                'why_participated' => $whyParticipated,
                'text' => $text,
                'file' => UploadedFile::fake()->image('photo.jpg', 450, 450),
                'details' => json_encode($details),
            ],
        );

        $response->assertCreated();
        $this->assertPostStructure($response);

        $textSignup = factory(Signup::class)->create();
        $textSignup->user = $photoSignup->user;
        $textSignup->northstar_id = $photoSignup->northstar_id;
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $whyParticipated = $this->faker->paragraph;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $textSignup->campaign_id,
            'post_type' => 'text',
        ]);

        // Create the post!
        $response = $this->asUser($textSignup->user)->postJson('api/v3/posts', [
            'northstar_id' => $textSignup->northstar_id,
            'campaign_id' => $textSignup->campaign_id,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $action->id,
            'quantity' => $quantity,
            'why_participated' => $whyParticipated,
            'text' => $text,
            'details' => json_encode($details),
        ]);

        $response->assertCreated();
        $this->assertPostStructure($response);

        $socialShareSignup = factory(Signup::class)->create();
        $socialShareSignup->user = $photoSignup->user;
        $socialShareSignup->northstar_id = $photoSignup->northstar_id;
        $quantity = $this->faker->numberBetween(10, 1000);
        $text = $this->faker->sentence;
        $details = ['source-detail' => 'broadcast-123', 'other' => 'other'];
        $action = factory(Action::class)->create([
            'campaign_id' => $socialShareSignup->campaign_id,
            'post_type' => 'share-social',
        ]);

        // Create the post!
        $response = $this->asUser($socialShareSignup->user)->postJson(
            'api/v3/posts',
            [
                'northstar_id' => $socialShareSignup->northstar_id,
                'campaign_id' => $socialShareSignup->campaign_id,
                'type' => $action->post_type,
                'action' => $action->name,
                'action_id' => $action->id,
                'quantity' => $quantity,
                'text' => $text,
                'details' => json_encode($details),
            ],
        );

        $response->assertCreated();
        $this->assertPostStructure($response);

        $user = $photoSignup->user->fresh();
        $this->assertEquals(
            ['signup', 'one-post', 'two-posts', 'three-posts'],
            $user->badges,
        );
    }
}
