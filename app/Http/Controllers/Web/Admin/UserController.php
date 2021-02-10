<?php

namespace App\Http\Controllers\Web\Admin;

use App\Auth\Registrar;
use App\Auth\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Types\CauseInterestType;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Northstar's user registrar.
     *
     * @var Registrar
     */
    protected $registrar;

    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

        $this->middleware('auth:web');
        $this->middleware('role:admin,staff,intern');

        $this->middleware('role:admin', ['only' => ['edit', 'update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::simplePaginate();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $this->authorize('view-full-profile', $user);

        return view('admin.users.show', [
            'user' => $user,
            'title' => $user->display_name,
        ]);
    }

    /**
     * Display the form for editing user information.
     *
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $this->authorize('edit-profile', $user);

        return view('admin.users.edit', [
            'user' => $user,
            'title' => $user->display_name,
            'causes' => CauseInterestType::labels(),
        ]);
    }

    /**
     * Making request to NorthstarAPI to update user's information.
     *
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(User $user, Request $request)
    {
        $this->authorize('edit-profile', $user);

        $input = normalize('credentials', $request);

        $this->registrar->validate($input, $user);

        // Format arrays & ensure they can be emptied out:
        $input['email_subscription_topics'] = !empty(
            $input['email_subscription_topics']
        )
            ? array_keys($input['email_subscription_topics'])
            : [];

        $input['causes'] = !empty($input['causes'])
            ? array_keys($input['causes'])
            : [];

        $input['feature_flags'] = !empty($input['feature_flags'])
            ? array_map('boolval', $input['feature_flags'])
            : [];

        // Only admins can change the role field.
        if ($input->has('role') && $input['role'] !== 'user') {
            Role::gate(['admin']);
        }

        $this->registrar->register($input->all(), $user);

        return redirect()
            ->route('admin.users.show', $user->id)
            ->with('flash', 'Sweet, look at you updating that user.');
    }

    /**
     * Delete a user from Northstar.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // @see: UserObserver@deleting
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('flash', 'User deleted.');
    }

    /**
     * Send a Northstar user a password reset email.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function sendPasswordReset(User $user, Request $request)
    {
        $this->authorize('delete', $user);

        $type = $request['type'];

        $user->sendPasswordReset($type);

        return redirect()
            ->route('admin.users.show', $user->id)
            ->with('flash', 'Sent ' . $type . ' email to user.');
    }
}
