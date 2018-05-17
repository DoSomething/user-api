<?php

namespace Northstar\Http\Transformers\Two;

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
            'first_name' => $user->first_name,
        ];

        if (Scope::allows('admin') || Gate::allows('view-full-profile', $user)) {
            $response['last_name'] = $user->last_name;
        }

        $response['last_initial'] = $user->last_initial;
        $response['photo'] = null;

        if (Scope::allows('admin') || Gate::allows('view-full-profile', $user)) {
            $response['email'] = $user->email;
            $response['mobile'] = format_legacy_mobile($user->mobile);
            $response['facebook_id'] = $user->facebook_id;

            $response['interests'] = [];
            $response['birthdate'] = format_date($user->birthdate, 'Y-m-d');

            $response['addr_street1'] = $user->addr_street1;
            $response['addr_street2'] = $user->addr_street2;
            $response['addr_city'] = $user->addr_city;
            $response['addr_state'] = $user->addr_state;
            $response['addr_zip'] = $user->addr_zip;

            // Signup source (e.g. cgg, mobile...)
            $response['source'] = $user->source;
            $response['source_detail'] = $user->source_detail;

            // Internal & third-party service IDs:
            $response['slack_id'] = null;

            // Subscription status
            $response['sms_status'] = $user->sms_status;
            $response['sms_paused'] = (bool) $user->sms_paused;

            // Voter registration status
            $response['voter_registration_status'] = $user->voter_registration_status;
        }

        $response['language'] = $user->language;
        $response['country'] = $user->country;

        $response['role'] = $user->role;

        if (Scope::allows('admin') || Gate::allows('view-full-profile', $user)) {
            $response['last_accessed_at'] = iso8601($user->last_accessed_at);
            $response['last_authenticated_at'] = iso8601($user->last_authenticated_at);
            $response['last_messaged_at'] = iso8601($user->last_messaged_at);
        }

        $response['updated_at'] = iso8601($user->updated_at);
        $response['created_at'] = iso8601($user->created_at);

        return $response;
    }
}
