<?php

namespace App\Http\Controllers;

use App\Auth\Role;
use App\Http\Transformers\SignupTransformer;
use App\Managers\SignupManager;
use App\Models\Campaign;
use App\Models\Signup;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SignupsController extends ActivityApiController
{
    /**
     * @var App\Http\Transformers\SignupTransformer;
     */
    protected $transformer;

    /**
     * The signup manager instance.
     *
     * @var \App\Manager\SignupManager
     */
    protected $signups;

    /**
     * Use cursor pagination for these routes.
     *
     * @var bool
     */
    protected $useCursorPagination = true;

    /**
     * Create a controller instance.
     *
     * @param SignupManager $signups
     */
    public function __construct(SignupManager $signups)
    {
        $this->signups = $signups;
        $this->transformer = new SignupTransformer();

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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'campaign_id' => 'required_without:action_id|integer',
            'action_id' => 'required_without:campaign_id|integer',
            'why_participated' => 'string',
            'referrer_user_id' => 'nullable|objectid',
        ]);

        $northstarId = getNorthstarId($request);

        // Get the campaign id from the request by campaign_id or action_id.
        $campaignId = $request['campaign_id']
            ? $request['campaign_id']
            : Campaign::fromActionId($request['action_id'])->id;

        // Check to see if the signup exists before creating one.
        $signup = $this->signups->get($northstarId, $campaignId);

        $code = $signup ? 200 : 201;

        if (!$signup) {
            $signup = $this->signups->create(
                $request->all(),
                $northstarId,
                $campaignId,
            );
        }

        return $this->item($signup, $code);
    }

    /**
     * Returns signups.
     * GET /signups.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->newQuery(Signup::class);

        $filters = $request->query('filter');
        $query = $this->filter($query, $filters, Signup::$indexes);

        // Only allow an admin or the user who owns the signup to see the signup's unapproved posts.
        if (Str::startsWith($request->query('include'), 'posts')) {
            $types = (new \League\Fractal\Manager())
                ->parseIncludes($request->query('include'))
                ->getIncludeParams('posts');

            $types = $types ? $types->get('type') : null;

            $query = $query->withVisiblePosts($types);
        }

        $orderBy = $request->query('orderBy');
        $query = $this->orderBy($query, $orderBy, Signup::$sortable);

        // Experimental: Allow paginating by cursor (e.g. `?cursor[after]=OTAxNg==`):
        if ($cursor = Arr::get($request->query('cursor'), 'after')) {
            $query->whereAfterCursor($cursor);

            // Using 'cursor' implies cursor pagination:
            $this->useCursorPagination = true;
        }

        return $this->paginatedCollection($query, $request);
    }

    /**
     * Returns a specific signup.
     * GET /signups/:id.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Signup $signup
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Signup $signup)
    {
        // Only allow an admin or the user who owns the signup to see the signup's unapproved posts.
        if (Str::startsWith($request->query('include'), 'posts')) {
            $types = (new \League\Fractal\Manager())
                ->parseIncludes($request->query('include'))
                ->getIncludeParams('posts');

            $types = $types ? $types->get('type') : null;

            $signup->load([
                'visiblePosts' => function ($query) use ($types) {
                    if ($types) {
                        $query->whereIn('type', $types);
                    }
                },
            ]);
        }

        return $this->item(
            $signup,
            200,
            [],
            $this->transformer,
            $request->query('include'),
        );
    }

    /**
     * Updates a specific signup.
     * PATCH /signups/:id.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Signup $signup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Signup $signup)
    {
        $validatedRequest = $this->validate($request, [
            'why_participated' => 'required',
        ]);

        // Only allow an admin or the user who owns the signup to update.
        // TODO: Should be using a policy method here.
        if (Role::allows(['admin']) || Auth::id() === $signup->northstar_id) {
            // why_participated is the only thing that can be changed
            $this->signups->update($signup, $validatedRequest);

            return $this->item($signup);
        }

        throw new AuthorizationException(
            'You don\'t have the correct role to update this signup!',
        );
    }

    /**
     * Delete a signup.
     * DELETE /signups/:id.
     *
     * @param \App\Models\Signup $signup
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Signup $signup)
    {
        $trashed = $this->signups->destroy($signup->id);

        if ($trashed) {
            return $this->respond('Signup deleted.', 200);
        }

        return response()->json([
            'code' => 500,
            'message' => 'There was an error deleting the post',
        ]);
    }
}
