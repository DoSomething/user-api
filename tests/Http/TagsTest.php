<?php

use App\Models\Post;

class TagsTest extends TestCase
{
    /**
     * Test that a POST request to /tags updates the post's tags and
     * creates a new event and tagged entry.
     *
     * POST /v3/posts/:post_id/tag
     * @return void
     */
    public function testTaggingAPost()
    {
        $post = factory(Post::class)->create();

        $response = $this->asAdminUser()->postJson(
            'api/v3/posts/' . $post->id . '/tags',
            [
                'tag_name' => 'Good Submission',
            ],
        );

        $response->assertOk();

        // Make sure that the post's tags are updated.
        $this->assertContains('Good Submission', $post->tagNames());
    }

    /**
     * Test that a POST request to /tags updates the post's tags.
     *
     * POST /v3/posts/:post_id/tag
     * @return void
     */
    public function testUntaggingAPost()
    {
        $post = factory(Post::class)->create();
        $post->tag('Good Submission');

        $response = $this->asAdminUser()->postJson(
            'api/v3/posts/' . $post->id . '/tags',
            [
                'tag_name' => 'Good Submission',
            ],
        );

        $response->assertOk();

        $this->assertEmpty($post->fresh()->tagNames());
    }

    /**
     * Test that a non-admin cannot tag a post.
     *
     * POST /v3/posts/:post_id/tag
     * @return void
     */
    public function testNormalUserCannotTagAPost()
    {
        $post = factory(Post::class)->create();

        $response = $this->asUser($post->user)->postJson(
            'api/v3/posts/' . $post->id . '/tags',
            [
                'tag_name' => 'Good Submission',
            ],
        );

        $response->assertUnauthorized();
    }

    /**
     * Test that a guest cannot tag a post.
     *
     * POST /v3/posts/:post_id/tag
     * @return void
     */
    public function testUnauthenticatedUserCannotTagAPost()
    {
        $post = factory(Post::class)->create();

        $response = $this->postJson('api/v3/posts/' . $post->id . '/tags', [
            'tag_name' => 'Good Submission',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test deleting one tag on a post only deletes that tag.
     *
     * POST /posts/:post_id/tag
     * @return void
     */
    public function testAddMultipleTagsAndDeleteOne()
    {
        $post = factory(Post::class)->create();

        $post->tag('Good Submission');
        $post->tag('Tag To Delete');

        $response = $this->asAdminUser()->postJson(
            'api/v3/posts/' . $post->id . '/tags',
            [
                'tag_name' => 'Tag To Delete',
            ],
        );

        $response->assertOk();

        $this->assertContains('Good Submission', $post->fresh()->tagNames());
        $this->assertNotContains('Tag To Delete', $post->fresh()->tagNames());
    }

    /**
     * Test post updated_at is updated when a new tag is applied to it.
     *
     * @return void
     */
    public function testPostTimestampUpdatedWhenTagAdded()
    {
        $post = factory(Post::class)->create([
            'created_at' => '2017-09-15 12:01:00',
            'updated_at' => '2017-09-15 12:01:00',
        ]);

        $this->mockTime('2018-10-21 13:05:00');

        $this->asAdminUser()->postJson('api/v3/posts/' . $post->id . '/tags', [
            'tag_name' => 'Good Submission',
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'id' => $post->id,
            'updated_at' => '2018-10-21 13:05:00',
        ]);
    }

    /**
     * Test withoutTag scope.
     *
     * @return void
     */
    public function testWithoutTagScope()
    {
        // Create the models that we will be using
        $posts = factory(Post::class, 20)->create();

        // Later, apply the tag to the post
        $this->asAdminUser()->postJson(
            'api/v3/posts/' . $posts->first()->id . '/tags',
            [
                'tag_name' => 'get-outta-here',
            ],
        );

        $postsQuery = Post::withoutTag('get-outta-here')->get();

        $this->assertEquals(19, $postsQuery->count());
    }
}
