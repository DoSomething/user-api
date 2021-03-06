<?php

namespace App\Http\Controllers;

use App\Http\Transformers\PostTransformer;
use App\Managers\PostManager;
use App\Models\Post;
use Illuminate\Http\Request;

class ReviewsController extends ActivityApiController
{
    /**
     * The post manager instance.
     *
     * @var App\Managers\PostManager
     */
    protected $post;

    /**
     * @var \App\Http\Transformers\PostTransformer
     */
    protected $transformer;

    /**
     * Create a controller instance.
     *
     * @param  PostManager $post
     * @return void
     */
    public function __construct(PostManager $post)
    {
        $this->post = $post;
        $this->transformer = new PostTransformer();

        $this->middleware('auth:api');

        $this->middleware('role:admin,staff');
        $this->middleware('scope:write');
        $this->middleware('scope:activity');
    }

    /**
     * Create a new review.
     *
     * @param Post $post
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Post $post, Request $request)
    {
        $request->validate([
            'status' => 'in:pending,accepted,rejected',
        ]);

        $reviewedPost = $this->post->review(
            $post,
            $request['status'],
            $request['comment'],
        );

        return $this->item($reviewedPost, 201);
    }
}
