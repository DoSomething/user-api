<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Northstar\Auth\Registrar;
use Northstar\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends BaseController
{
    /**
     * The registrar.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * Make a new UserController, inject dependencies and
     * set middleware for this controller's methods.
     *
     * @param Registrar $registrar
     */
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

        $this->middleware('auth:web');
        $this->middleware('role:admin,staff', ['only' => ['show']]);
    }

    /**
     * Show the homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        return view('users.show', ['user' => auth()->guard('web')->user()]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // @TODO: Implement this route.
        return redirect('/');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        if (! $user->can('editProfile', [auth()->guard('web')->user(), $user])) {
            throw new AccessDeniedHttpException;
        }

        return view('users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (! $user->can('editProfile', [auth()->guard('web')->user(), $user])) {
            throw new AccessDeniedHttpException;
        }

        $this->registrar->validate($request, $user, [
            'first_name' => 'required|max:50',
            'last_name' => 'nullable|max:50',
            'birthdate' => 'nullable|required|date',
            'password' => 'nullable|min:6|max:512|confirmed', // @TODO: Split into separate form.
        ]);

        // Remove fields with empty values.
        $values = array_diff($request->all(), ['']);

        $user->fill($values)->save();

        return redirect('/');
    }
}
