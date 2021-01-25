<?php

namespace App\Http\Controllers;

use App\Http\Transformers\ReactionTransformer;
use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends ActivityApiController
{
    /**
     * @var App\Http\Transformers\ReactionTransformer;
     */
    protected $transformer;

    /**
     * Use cursor pagination for these routes.
     *
     * @var bool
     */
    protected $useCursorPagination = true;

    /**
     * Create a controller instance.
     */
    public function __construct()
    {
        $this->transformer = new ReactionTransformer();

        $this->middleware('scopes:activity');
        $this->middleware('auth:api', ['only' => ['store']]);
        $this->middleware('scopes:write', ['only' => ['store']]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Post $post)
    {
        $northstarId = getNorthstarId($request);

        // Check to see if the post has a reaction from this particular user with id of northstar_id. If not, create one.
        $reaction = Reaction::withTrashed()->firstOrCreate([
            'northstar_id' => $northstarId,
            'post_id' => $post->id,
        ]);

        if ($reaction->wasRecentlyCreated || $reaction->trashed()) {
            // We're adding a new reaction in these cases.
            $code = 200;
            $action = 'liked';

            if ($reaction->trashed()) {
                $reaction->restore();
                $code = 201;
            }
        } else {
            // Otherwise, we must be removing.
            $code = 201;
            $action = 'unliked';
            $reaction->delete();
        }

        $meta = [
            'action' => $action,
            'total_reactions' => $post->getTotalReactions($post->id),
        ];

        return $this->item($reaction, $code, $meta);
    }

    /**
     * Returns all reactions for a post.
     * GET /post/:post_id/reactions.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Post $post
     * @return Illuminate\Http\Response
     */
    public function index(Request $request, Post $post)
    {
        $query = Reaction::withTrashed()->where(['post_id' => $post->id]);

        return $this->paginatedCollection($query, $request);
    }
}
