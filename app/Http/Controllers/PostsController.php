<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersActivityRequests;
use App\Http\Controllers\Traits\FiltersRequests;
use App\Http\Requests\PostRequest;
use App\Http\Transformers\PostTransformer;
use App\Managers\PostManager;
use App\Managers\SignupManager;
use App\Models\Campaign;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class PostsController extends ActivityApiController
{
    use FiltersActivityRequests;

    /**
     * The post manager instance.
     *
     * @var \App\Managers\PostManager
     */
    protected $posts;

    /**
     * The SignupManager instance.
     *
     * @var \App\Managers\SignupManager
     */
    protected $signups;

    /**
     * @var \App\Http\Transformers\PostTransformer;
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
     *
     * @param PostManager $posts
     * @param SignupManager $signups
     * @param PostTransformer $transformer
     */
    public function __construct(
        PostManager $posts,
        SignupManager $signups,
        PostTransformer $transformer
    ) {
        $this->posts = $posts;
        $this->signups = $signups;
        $this->transformer = $transformer;

        $this->middleware('scope:activity');
        $this->middleware('auth:api', [
            'only' => ['store', 'update', 'destroy'],
        ]);
        $this->middleware('role:admin,staff', ['only' => ['destroy']]);
        $this->middleware('scope:write', [
            'only' => ['store', 'update', 'destroy'],
        ]);
    }

    /**
     * Returns Posts, filtered by params, if provided.
     * GET /posts.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = $this->newQuery(Post::class);

        if (has_include($request, 'signup')) {
            // Eagerly load the `signup` relationship.
            $query->with('signup');
        }

        $filters = $request->query('filter');
        $query = $this->filter($query, $filters, Post::$indexes);

        // Only allow admins, staff, or owner to see un-approved posts from other users.
        $query = $query->whereVisible();

        // Only return posts tagged "Hide In Gallery" if staff user or if is owner of the post.
        $query = $query->withHiddenPosts();

        // If tag param is passed, only return posts that have that tag.
        if (Arr::has($filters, 'tag')) {
            $query = $query->withTag($filters['tag']);
        }

        if (Arr::has($filters, 'volunteer_credit')) {
            if (
                filter_var(
                    $filters['volunteer_credit'],
                    FILTER_VALIDATE_BOOLEAN,
                )
            ) {
                $query = $query->withVolunteerCredit(
                    $filters['volunteer_credit'],
                );
            } else {
                $query = $query->withoutVolunteerCredit(
                    $filters['volunteer_credit'],
                );
            }
        }

        // If the northstar_id param is passed, only allow admins, staff, or owner to see anonymous posts.
        if (Arr::has($filters, 'northstar_id')) {
            $query = $query->withoutAnonymousPosts();
        }

        // This endpoint always returns posts in reverse chronological order. We'll
        // therefore "force" the query string so that we can use it in `getCursor`.
        // @TODO: There must be a more elegant way of doing this...
        $query->orderBy('created_at', 'desc')->orderBy('id', 'asc');

        $request->query->set('orderBy', 'created_at,desc');

        // Experimental: Allow paginating by cursor (e.g. `?cursor[after]=OTAxNg==`):
        if ($cursor = Arr::get($request->query('cursor'), 'after')) {
            $query->whereAfterCursor($cursor);

            // Using 'cursor' implies cursor pagination:
            $this->useCursorPagination = true;
        }

        return $this->paginatedCollection($query, $request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  PostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        $northstarId = getNorthstarId($request);

        // Get the campaign id from the request by campaign_id or action_id.
        $campaignId = $request['campaign_id']
            ? $request['campaign_id']
            : Campaign::fromActionId($request['action_id'])->id;

        $signup = $this->signups->get($northstarId, $campaignId);

        if (!$signup) {
            $signup = $this->signups->create(
                $request->all(),
                $northstarId,
                $campaignId,
            );
        }

        $post = $this->posts->create($request->all(), $signup->id);

        return $this->item($post, 201, [], null, 'signup');
    }

    /**
     * Returns a specific post.
     * GET /posts/:id.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Post $post)
    {
        // Only allow an admin or the user who owns the post to see their own unapproved posts.
        $this->authorize('show', $post);

        return $this->item($post);
    }

    /**
     * Updates a specific post.
     * PATCH /posts/:id.
     *
     * @param PostRequest $request
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PostRequest $request, Post $post)
    {
        // Only allow an admin/staff or the user who owns the post to update.
        $this->authorize('update', $post);

        $validatedRequest = $request->validated();

        // But don't allow user's to review their own posts.
        if (!Gate::allows('review', $post)) {
            unset($validatedRequest['status']);
        }

        $this->posts->update($post, $validatedRequest);

        return $this->item($post);
    }

    /**
     * Delete a post.
     * DELETE /posts/:id.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Post $post)
    {
        $this->posts->destroy($post);

        return $this->respond('Post deleted.', 200);
    }
}
