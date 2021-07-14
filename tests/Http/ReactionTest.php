<?php

namespace Tests\Http;

use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Tests\TestCase;

class ReactionTest extends TestCase
{
    /**
     * Test for retrieving all reactions of a post.
     *
     * GET /api/v3/post/:post_id/reactions
     * @return void
     */
    public function testReactionsIndex()
    {
        $post = factory(Post::class)->create();
        factory(Reaction::class, 10)->create(['post_id' => $post->id]);

        $response = $this->getJson('api/v3/posts/' . $post->id . '/reactions');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'northstar_id',
                    'post_id',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
        ]);
    }

    /**
     * Test that the POST /reactions request creates a reaction for a post.
     * Also test that when POST /reactions is hit again by the same user
     * for the same post, the reaction is soft deleted.
     *
     * POST /reactions
     * @return void
     */
    public function testPostingAndSoftDeleteForReaction()
    {
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        // Create a reaction.
        $response = $this->asUser($user)->postJson(
            'api/v3/posts/' . $post->id . '/reactions',
        );

        $response->assertSuccessful(); // TODO: This should be a 201.
        $response->assertJson([
            'meta' => [
                'total_reactions' => 1,
            ],
        ]);

        // If we "react" to the same post again, it should "un-like" it:
        $response = $this->asUser($user)->postJson(
            'api/v3/posts/' . $post->id . '/reactions',
        );

        $response->assertSuccessful(); // TODO: This should be a 200.
        $response->assertJson([
            'meta' => [
                'total_reactions' => 0,
            ],
        ]);
    }

    /**
     * Test that the aggregate of total reactions for a post is correct.
     *
     * POST /reactions
     * @return void
     */
    public function testAggregateReactions()
    {
        // Given a post with 3 reactions...
        $post = factory(Post::class)->create();
        factory(Reaction::class, 3)->create(['post_id' => $post->id]);

        // When we add a new reaction...
        $response = $this->asNormalUser()->postJson(
            'api/v3/posts/' . $post->id . '/reactions',
        );

        // We should see an increased total!
        $response->assertSuccessful();
        $response->assertJson([
            'meta' => [
                'total_reactions' => 4,
            ],
        ]);
    }

    /**
     * Test that a post and signup's updated_at updates when a reaction is made.
     *
     * @return void
     */
    public function testUpdatedPostWithReaction()
    {
        // Given a post that was last updated a long time ago...
        $post = factory(Post::class)->create([
            'updated_at' => '2017-08-03 14:00:00',
            'created_at' => '2017-08-03 14:00:00',
        ]);

        $reaction = factory(Reaction::class)->create([
            'post_id' => $post->id,
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'id' => $post->id,
            'updated_at' => $reaction->created_at,
        ]);
    }

    /**
     * Test that non-authenticated user's/apps can't create reactions.
     *
     * @return void
     */
    public function testUnauthenticatedUserCreatingAReaction()
    {
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        $response = $this->postJson(
            'api/v3/posts/' . $post->id . '/reactions',
            [
                'northstar_id' => $user->id,
            ],
        );

        $response->assertUnauthorized();
    }
}
