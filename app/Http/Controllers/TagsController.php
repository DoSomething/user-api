<?php

namespace App\Http\Controllers;

use App\Http\Transformers\PostTransformer;
use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagsController extends ActivityApiController
{
    /**
     * The post service instance.
     *
     * @var App\Repositories\PostRepository
     */
    protected $post;

    /**
     * @var App\Http\Transformers\PostTransformer;
     */
    protected $transformer;

    /**
     * Create a controller instance.
     *
     * @param  PostContract $posts
     * @return void
     */
    public function __construct(PostRepository $post)
    {
        $this->post = $post;
        $this->transformer = new PostTransformer();

        $this->middleware('auth:api');
        $this->middleware('role:admin,staff');
        $this->middleware('scopes:write');
        $this->middleware('scopes:activity');
    }

    /**
     * Updates a post's tags when added or deleted.
     *
     * @param  Post $post
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Post $post, Request $request)
    {
        $request->validate([
            'tag_name' => 'required|string',
        ]);

        // If a tag slug is sent in (dashed or lowercase), change to the tag name.
        // @TODO: This controller/model should really deal in slugs...
        $tag = $request->tag_name;
        if (Str::contains($tag, '-') || ctype_lower($tag)) {
            $tag = ucwords(str_replace('-', ' ', $tag));
        }

        // If the post already has the tag, remove it. Otherwise, add the tag to the post.
        if ($post->tagNames()->contains($tag)) {
            $updatedPost = $this->post->untag($post, $tag);
        } else {
            $updatedPost = $this->post->tag($post, $tag);
        }

        return $this->item($updatedPost);
    }
}
