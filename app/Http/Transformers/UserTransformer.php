<?php

namespace Northstar\Http\Transformers;

use Northstar\Auth\Scope;
use Northstar\Models\User;
use League\Fractal\TransformerAbstract;
use Gate;

class UserTransformer extends TransformerAbstract
{
    /**
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        $response = [
            'id' => $user->_id,
            '_id' => $user->_id, // @DEPRECATED: Will be removed.

            'first_name' => $user->first_name,
        ];

        if (Scope::allows('admin') || Gate::allows('view-full-profile', $user)) {
            $response['last_name'] = $user->last_name;
        }

        $response['last_initial'] = $user->last_initial;
        $response['photo'] = $user->photo;

        if (Scope::allows('admin') || Gate::allows('view-full-profile', $user)) {
            $response['email'] = $user->email;
            $response['mobile'] = $user->mobile;
            $response['facebook_id'] = $user->facebook_id;

            $response['interests'] = $user->interests;
            $response['birthdate'] = format_date($user->birthdate, 'Y-m-d');

            $response['addr_street1'] = $user->addr_street1;
            $response['addr_street2'] = $user->addr_street2;
            $response['addr_city'] = $user->addr_city;
            $response['addr_state'] = $user->addr_state;
            $response['addr_zip'] = $user->addr_zip;

            // Signup source (e.g. drupal, cgg, mobile...)
            $response['source'] = $user->source;

            // Internal & third-party service IDs:
            $response['slack_id'] = $user->slack_id;
            $response['mobilecommons_id'] = $user->mobilecommons_id;
            $response['parse_installation_ids'] = $user->parse_installation_ids;
            $response['mobilecommons_status'] = $user->mobilecommons_status;
        }

        $response['language'] = $user->language;
        $response['country'] = $user->country;

        // Drupal ID for this user. Used in the mobile app.
        $response['drupal_id'] = $user->drupal_id;
        $response['role'] = $user->role;

        $response['updated_at'] = $user->updated_at->toIso8601String();
        $response['created_at'] = $user->created_at->toIso8601String();

        return $response;
    }
}
