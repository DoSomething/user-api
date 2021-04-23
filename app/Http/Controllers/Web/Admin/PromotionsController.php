<?php

namespace App\Http\Controllers\Web\Admin;

use App\Auth\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PromotionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
        $this->middleware('role:admin,staff,intern');

        $this->middleware('role:admin', ['only' => ['edit', 'update']]);
    }

    /**
     * Mutes promotions for user.
     *
     * @TODO: this existing as a destroy method seems odd, since it is not directly
     * deleting a resource but setting a property on the User model. Contextually,
     * this will ultimately result in the deletion of the user from CustomerIO, but
     * could be a bit clearer.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        logger('Muting promotions', ['user' => $user->id]);

        $user->mutePromotions();

        return redirect()
            ->route('admin.users.show', $user->id)
            ->with('flash', 'Promotions muted for user. Shhh....');
    }
}
