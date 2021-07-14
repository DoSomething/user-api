<?php

namespace Tests\Http;

use App\Models\Action;
use App\Models\Post;
use App\Models\User;
use Tests\TestCase;

class ReviewsTest extends TestCase
{
    /**
     * Test that we can review posts.
     *
     * POST /reviews
     * @return void
     */
    public function testPostingReview()
    {
        $admin = factory(User::class)->states('admin')->create();

        $schoolId = $this->faker->school_id;

        $post = factory(Post::class)
            ->states('photo', 'pending')
            ->create([
                'school_id' => $schoolId,
            ]);

        $response = $this->asUser($admin)->postJson(
            '/api/v3/posts/' . $post->id . '/reviews',
            [
                'status' => 'accepted',
                'comment' => 'Testing 1st review',
            ],
        );

        $response->assertCreated();

        $this->assertCustomerIoEvent($post->user, 'campaign_review');

        $this->assertMysqlDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'accepted',
        ]);

        $this->assertMysqlDatabaseHas('reviews', [
            'admin_northstar_id' => $admin->id,
            'post_id' => $post->id,
            'comment' => 'Testing 1st review',
        ]);
    }

    /**
     * Test that reviewing posts updates aggregate quantities.
     *
     * POST /reviews
     * @return void
     */
    public function testReviewsUpdateAggregateQuantities()
    {
        $action = factory(Action::class)->create();
        $schoolId = $this->faker->school_id;

        [$firstPost, $secondPost] = factory(Post::class, 2)
            ->states('photo', 'pending')
            ->create([
                'action_id' => $action->id,
                'campaign_id' => $action->campaign->id,
                'school_id' => $schoolId,
            ]);

        $this->asAdminUser()->postJson(
            '/api/v3/posts/' . $firstPost->id . '/reviews',
            [
                'status' => 'accepted',
            ],
        );

        $this->asAdminUser()->postJson(
            '/api/v3/posts/' . $secondPost->id . '/reviews',
            [
                'status' => 'accepted',
            ],
        );

        $this->assertMysqlDatabaseHas('campaigns', [
            'id' => $action->campaign->id,
            'accepted_count' => 2,
            'pending_count' => 0,
        ]);

        $this->assertMysqlDatabaseHas('action_stats', [
            'action_id' => $action->id,
            'impact' => $firstPost->quantity + $secondPost->quantity,
            'school_id' => $schoolId,
        ]);
    }

    /**
     * Test that non-admin cannot review posts.
     *
     * @return void
     */
    public function testNormalUserCantReviewPosts()
    {
        $post = factory(Post::class)->create();

        $response = $this->asUser($post->user)->postJson(
            '/api/v3/posts/' . $post->id . '/reviews',
            [
                'status' => 'accepted',
            ],
        );

        $response->assertUnauthorized();
    }

    /**
     * Test that anonymous users cannot review posts.
     *
     * @return void
     */
    public function testUnauthenticatedUserCantReviewPosts()
    {
        $post = factory(Post::class)->create();

        $response = $this->postJson('/api/v3/posts/' . $post->id . '/reviews', [
            'status' => 'accepted',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test that you get a 404 if the post doesn't exist.
     *
     * @return void
     */
    public function test404IfPostDoesntExist()
    {
        $response = $this->asAdminUser()->postJson(
            '/api/v3/reviews/posts/8675309/reviews',
            [
                'status' => 'accepted',
            ],
        );

        $response->assertNotFound();
    }

    /**
     * Test that a post and signup's updated_at updates when a review is made.
     *
     * @return void
     */
    public function testUpdatedPostAndSignupWithReview()
    {
        $post = factory(Post::class)->create([
            'created_at' => '2019-02-15 14:32:00',
            'updated_at' => '2019-02-15 14:32:00',
        ]);

        $this->mockTime('8/3/2020 16:55:00');

        $this->asAdminUser()->postJson(
            '/api/v3/posts/' . $post->id . '/reviews',
            [
                'status' => 'accepted',
            ],
        );

        $this->assertMysqlDatabaseHas('posts', [
            'id' => $post->id,
            'updated_at' => '2020-08-03 16:55:00',
        ]);
    }
}
