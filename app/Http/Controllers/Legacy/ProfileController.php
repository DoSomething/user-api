<?php

namespace Northstar\Http\Controllers\Legacy;

use Illuminate\Contracts\Auth\Guard as Auth;
use Northstar\Auth\Registrar;
use Northstar\Http\Controllers\Controller;
use Northstar\Http\Transformers\Legacy\UserTransformer;
use Illuminate\Http\Request;
use Northstar\Models\User;

class ProfileController extends Controller
{
    /**
     * The registrar.
     * @var Registrar
     */
    protected $registrar;

    /**
     * The authentication guard.
     * @var Auth
     */
    protected $auth;

    /**
     * @var UserTransformer
     */
    protected $transformer;

    public function __construct(Auth $auth, Registrar $registrar)
    {
        $this->auth = $auth;
        $this->registrar = $registrar;

        $this->transformer = new UserTransformer();

        $this->middleware('auth');
        $this->middleware('scope:write', ['only' => ['update']]);
    }

    /**
     * Display the current user's profile.
     * GET /profile
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        /** @var \Northstar\Models\User $user */
        $user = $this->auth->user();

        return $this->item($user);
    }

    /**
     * Update the currently authenticated user's profile.
     * PUT /profile
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        /** @var \Northstar\Models\User $user */
        $user = $this->auth->user();

        // Normalize & validate the given request.
        $request = normalize('credentials', $request);
        $this->registrar->validate($request, $user);

        $user->fill($request->except(User::$internal));
        $user->save();

        return $this->item($user);
    }
}
