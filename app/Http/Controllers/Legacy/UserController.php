<?php

namespace Northstar\Http\Controllers\Legacy;

use Northstar\Auth\Role;
use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Illuminate\Support\Facades\Log;
use Northstar\Http\Controllers\Controller;
use Northstar\Http\Transformers\Legacy\UserTransformer;
use Northstar\Exceptions\NorthstarValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    /**
     * The registrar handles creating, updating, and
     * validating user accounts.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * @var UserTransformer
     */
    protected $transformer;

    /**
     * Make a new UserController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param Registrar $registrar
     * @param UserTransformer $transformer
     */
    public function __construct(Registrar $registrar, UserTransformer $transformer)
    {
        $this->registrar = $registrar;
        $this->transformer = $transformer;

        $this->middleware('role:admin,staff', ['except' => ['show']]);
        $this->middleware('scope:user');
        $this->middleware('scope:write', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     * GET /users
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Create an empty User query, which we can either filter (below)
        // or paginate to retrieve all user records.
        $query = $this->newQuery(User::class);

        // Use `?filter[column]=value` for exact matches.
        $filters = normalize('credentials', $request->query('filter'));
        $query = $this->filter($query, $filters, User::$indexes);

        // Use `?before[column]=time` to get records before given value.
        $befores = normalize('dates', $request->query('before'));
        $query = $this->filter($query, $befores, [User::CREATED_AT, User::UPDATED_AT], '<');

        // Use `?after[column]=time` to get records after given value.
        $afters = normalize('dates', $request->query('after'));
        $query = $this->filter($query, $afters, [User::CREATED_AT, User::UPDATED_AT], '>');

        // Use `?search[column]=value` or `?search=key` to find users matching one or more criteria.
        $searches = $request->query('search');

        $query = $this->search($query, normalize('credentials', $searches), User::$uniqueIndexes);

        return $this->paginatedCollection($query, $request);
    }

    /**
     * Store a newly created resource in storage.
     * POST /users
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws NorthstarValidationException
     */
    public function store(Request $request)
    {
        // This endpoint will upsert by default (so it will either create a new user, or
        // update a user if one with a matching index field is found).
        $existingUser = $this->registrar->resolve($request->only('id', 'email', 'mobile', 'drupal_id', 'facebook_id'));

        // If `?upsert=false` and a record already exists, return a custom validation error.
        if (! filter_var($request->query('upsert', 'true'), FILTER_VALIDATE_BOOLEAN) && $existingUser) {
            throw new NorthstarValidationException(['id' => ['A record matching one of the given indexes already exists.']], $existingUser);
        }

        // Normalize input and validate the request
        $request = normalize('credentials', $request);
        $this->registrar->validate($request, $existingUser);

        // Makes sure we can't "upsert" a record to have a changed index if already set.
        // @TODO: There must be a better way to do this...
        foreach (User::$uniqueIndexes as $index) {
            if ($request->has($index) && ! empty($existingUser->{$index}) && $request->input($index) !== $existingUser->{$index}) {
                Log::warning('upsert_index_conflict', [
                    'index' => $index,
                    'new' => $request->input($index),
                    'existing' => $existingUser->{$index},
                ]);

                throw new NorthstarValidationException([$index => ['Cannot upsert an existing index.']], $existingUser);
            }
        }

        $user = $this->registrar->register($request->except('role'), $existingUser, function (User $user) use ($request, $existingUser) {
            // Only save a source if not upserting a user.
            if ($request->has('source') && ! $existingUser) {
                $user->setSource($request->input('source'), $request->input('source_detail'));
            }
        });

        $code = ! is_null($existingUser) ? 200 : 201;

        return $this->item($user, $code);
    }

    /**
     * Display the specified resource.
     * GET /users/:term/:id
     *
     * @param string $term - term to search by (eg. mobile, drupal_id, id, email, etc)
     * @param string $id - the actual value to search for
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function show($term, $id)
    {
        // Restrict username/email/mobile/facebook_id profile lookup to admin or staff.
        if (in_array($term, ['username', 'email', 'mobile', 'facebook_id'])) {
            Role::gate(['admin', 'staff']);
        }

        $user = $this->registrar->resolveOrFail([$term => $id]);

        return $this->item($user);
    }

    /**
     * Update the specified resource in storage.
     * PUT /users/:term/:id
     *
     * @param string $term - term to search by (eg. mobile, drupal_id, id, email, etc)
     * @param string $id - the actual value to search for
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update($term, $id, Request $request)
    {
        $user = $this->registrar->resolveOrFail([$term => $id]);

        // Normalize input and validate the request
        $request = normalize('credentials', $request);
        $this->registrar->validate($request, $user);

        // Only admins can change the role field.
        if ($request->has('role') && $request->input('role') !== 'user') {
            Role::gate(['admin']);
        }

        $this->registrar->register($request->all(), $user);

        return $this->item($user);
    }

    /**
     * Delete a user resource.
     * DELETE /users/:id
     *
     * @param $id - User ID
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return $this->respond('No Content.');
    }
}
