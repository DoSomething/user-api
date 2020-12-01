<?php

namespace App\Http\Controllers\Legacy;

use App\Auth\Registrar;
use App\Http\Controllers\Controller;
use App\Http\Transformers\Legacy\UserTransformer;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard as Auth;
use Illuminate\Http\Request;

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
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = $this->auth->user();

        return $this->item($user);
    }

    /**
     * Update the currently authenticated user's profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $this->auth->user();

        // Normalize & validate the given request.
        $request = normalize('credentials', $request);
        $this->registrar->validate($request, $user);

        $user->fill($request->except(User::$internal));
        $user->save();

        return $this->item($user);
    }
}
