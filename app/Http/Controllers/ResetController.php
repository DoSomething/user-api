<?php

namespace Northstar\Http\Controllers;

use Northstar\Models\User;
use Northstar\PasswordResetType;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Auth\DatabaseTokenRepository;

class ResetController extends Controller
{
    /**
     * Make a new ResetController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct()
    {
        $this->middleware('role:admin');
        $this->middleware('scope:write', ['only' => ['store']]);
    }

    /**
     * Sends a password reset email.
     * POST /resets
     *
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'type' => ['required', Rule::in(PasswordResetType::all())],
        ]);

        /** @var \Northstar\Models\User $user */
        $user = User::findOrFail($request['id']);

        $tokenRepository = $this->createTokenRepository();
        $token = $tokenRepository->create($user);
        $message = $user->sendPasswordReset($token, $request['type']);

        return $this->respond('Message sent.');
    }

    /**
     * Create a token repository instance based on the given configuration.
     *
     * @return DatabaseTokenRepository
     */
    protected function createTokenRepository()
    {
        return new DatabaseTokenRepository(
            app('db')->connection(),
            app('hash'),
            config('auth.passwords.users.table'),
            config('app.key'),
            config('auth.passwords.users.expire')
        );
    }
}
