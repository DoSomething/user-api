<?php

namespace App\Http\Controllers;

class UserInfoController extends Controller
{
    /**
     * Make a new UserInfoController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Show the user info for the authorized user.
     *
     * @return array
     */
    public function show()
    {
        $user = auth()->user();

        // User data, formatted according to OpenID Connect spec, section 5.3.
        // @see http://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
        return [
            'sub' => $user->id,
            'given_name' => $user->first_name,
            'family_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => format_legacy_mobile($user->mobile),
            'birthdate' => format_date($user->birthdate, 'Y-m-d'),
            'updated_at' => $user->updated_at->timestamp,
            'created_at' => $user->created_at->timestamp,
        ];
    }
}
