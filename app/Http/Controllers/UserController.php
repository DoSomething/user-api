<?php

namespace Northstar\Http\Controllers;

use Northstar\Auth\Role;
use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\AuthenticationException;
use Northstar\Http\Transformers\UserTransformer;
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

        $this->middleware('role:admin,staff', ['except' => ['show', 'update']]);
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
        $existingUser = $this->registrar->resolve($request->only('id', 'email', 'mobile', 'drupal_id', 'facebook_id'));

        // If there is an existing user, throw an error.
        if ($existingUser && ! $request->query('upsert')) {
            throw new NorthstarValidationException(['id' => ['A record matching one of the given indexes already exists.']], $existingUser);
        }

        // Normalize input and validate the request
        $request = normalize('credentials', $request);
        $this->registrar->validate($request, $existingUser);

        // If `?upsert=true` and a record already exists, update a user with the $request fields.
        if ($request->query('upsert') && $existingUser) {
            // Makes sure we can't "upsert" a record to have a changed index if already set.
            // @TODO: There must be a better way to do this...
            foreach (User::$uniqueIndexes as $index) {
                if ($request->has($index) && ! empty($existingUser->{$index}) && $request->input($index) !== $existingUser->{$index}) {
                    Log::warning('upsert_index_conflict', [
                        'index' => $index,
                        'new' => $request->input($index),
                        'existing' => $existingUser->{$index},
                    ]);

                    throw new NorthstarValidationException([$index => ['Cannot upsert an existing index.']], $user);
                }
            }
        }

        $user = $this->registrar->register($request->except('role'), $existingUser);

        $code = ! is_null($existingUser) ? 200 : 201;

        return $this->item($user, $code);
    }

    /**
     * Display the specified resource.
     * GET /users/:id
     *
     * @param string $id - the actual value to search for
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return $this->item($user);
    }

    /**
     * Update the specified resource in storage.
     * PUT /users/:id
     *
     * @param string $id - the actual value to search for
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $user = User::findOrFail($id);

        if (! Gate::allows('edit-profile', $user)) {
            throw new AuthenticationException('This action is unauthorized.');
        }

        // Debug level log to show the payload received
        Log::debug('received update user payload for user '.$user->id, $request->all());

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
     * @param User $user
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // @see: UserObserver@deleting
        $user->delete();

        // We'll return the deleted user since that may be helpful
        // for some applications to update their local state.
        return $this->item($user);
    }
}
