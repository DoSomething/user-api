<?php

namespace Northstar\Http\Controllers;

use Jenssegers\Mongodb\Auth\DatabaseTokenRepository;
use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Services\Phoenix;

class ResetController extends Controller
{
    /**
     * Phoenix Drupal API wrapper.
     *
     * @var Phoenix
     */
    protected $phoenix;

    /**
     * Make a new ResetController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param Phoenix $phoenix
     */
    public function __construct(Phoenix $phoenix)
    {
        $this->phoenix = $phoenix;

        $this->middleware('role:admin');
        $this->middleware('scope:write', ['only' => ['store']]);
    }

    /**
     * Create a new password reset token.
     * POST /users
     *
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
        ]);

        /** @var \Northstar\Models\User $user */
        $user = User::findOrFail($request['id']);

        $tokenRepository = $this->createTokenRepository();
        $token = $tokenRepository->create($user);
        $email = $user->getEmailForPasswordReset();

        return [
            'url' => config('app.url').'/password/reset/'.$token.'?email='.urlencode($email),
        ];
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
