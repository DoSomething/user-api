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

        $user->sendPasswordReset($request['type']);

        return $this->respond('Message sent.');
    }
}
