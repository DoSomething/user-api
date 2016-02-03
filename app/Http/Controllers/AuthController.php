<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Http\Transformers\TokenTransformer;
use Northstar\Http\Transformers\UserTransformer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Northstar\Auth\Registrar;
use Auth;

class AuthController extends Controller
{
    /**
     * The registrar.
     * @var Registrar
     */
    protected $registrar;

    /**
     * @var TokenTransformer
     */
    protected $transformer;

    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

        $this->transformer = new TokenTransformer();

        $this->middleware('key:user');
        $this->middleware('auth', ['only' => 'logout']);
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Authenticate a registered user
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws UnauthorizedHttpException
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|required_without:mobile',
            'mobile' => 'required_without:email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'mobile', 'password');
        $token = $this->registrar->login($credentials);

        return $this->item($token);
    }

    /**
     * Logout the current user by invalidating their session token.
     * POST /logout
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function logout(Request $request)
    {
        $token = Auth::token();

        // Attempt to delete token.
        $deleted = $token->delete();
        if (! $deleted) {
            throw new HttpException(400, 'User could not log out. Please try again.');
        }

        // Remove Parse installation ID. Disables push notifications.
        $user = $token->user;
        if ($user && $request->has('parse_installation_ids')) {
            $removeIds = $request->input('parse_installation_ids');
            $user->pull('parse_installation_ids', $removeIds);
            $user->save();
        }

        return $this->respond('User logged out successfully.');
    }

    /**
     * Authenticate a registered user
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws UnauthorizedHttpException
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|max:60|unique:users|required_without:mobile',
            'mobile' => 'unique:users|required_without:email',
        ]);

        $user = $this->registrar->register($request->all());

        // Should we try to make a Drupal account for this user?
        if ($request->has('create_drupal_user') && $request->has('password') && ! $user->drupal_id) {
            $user = $this->registrar->createDrupalUser($user, $request->input('password'));
            $user->save();
        }

        $token = $this->registrar->createToken($user);

        return $this->item($token);
    }
}
