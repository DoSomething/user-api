<?php

namespace Northstar\Http\Transformers\Legacy;

use Northstar\Models\User;
use Illuminate\Support\Facades\Gate;
use League\Fractal\TransformerAbstract;

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

        if (Gate::allows('view-full-profile', $user)) {
            $response['last_name'] = $user->last_name;
        }

        $response['last_initial'] = $user->last_initial;
        $response['photo'] = null;

        if (Gate::allows('view-full-profile', $user)) {
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

            // Source for the address fields (e.g. 'sms')
            $response['addr_source'] = $user->addr_source;

            // Signup source (e.g. drupal, cgg, mobile...)
            $response['source'] = $user->source;
            $response['source_detail'] = $user->source_detail;

            // Internal & third-party service IDs:
            $response['slack_id'] = null;
            $response['mobilecommons_id'] = $user->mobilecommons_id;
            $response['mobilecommons_status'] = $user->sms_status; // @DEPRECATED: Will be removed.
            $response['parse_installation_ids'] = []; // @DEPRECATED.

            // Email subscription statuses
            $response['email_subscription_status'] = (bool) $user->email_subscription_status;
            $response['email_subscription_topics'] = $user->email_subscription_topics;

            //Cause Areas
            $response['causes'] = $user->causes;

            // Voter registration status
            $response['voter_registration_status'] = $user->voter_registration_status;

            // Voting Plan Status
            $response['voting_plan_status'] = $user->voting_plan_status;

            // Feature Flags
            $response['feature_flags'] = $user->feature_flags;
        }

        // Make a Voting Plan fields to be rendered in messaging
        $response['voting_plan_method_of_transport'] = $user->voting_plan_method_of_transport;
        $response['voting_plan_time_of_day'] = $user->voting_plan_time_of_day;
        $response['voting_plan_attending_with'] = $user->voting_plan_attending_with;

        $response['language'] = $user->language;
        $response['country'] = $user->country;

        // Drupal ID for this user. Used in the mobile app.
        $response['drupal_id'] = $user->drupal_id;
        $response['role'] = $user->role;

        // SMS subscription status
        $response['sms_status'] = $user->sms_status;
        $response['sms_paused'] = (bool) $user->sms_paused;

        if (Gate::allows('view-full-profile', $user)) {
            $response['last_accessed_at'] = iso8601($user->last_accessed_at);
            $response['last_authenticated_at'] = iso8601($user->last_authenticated_at);
            $response['last_messaged_at'] = iso8601($user->last_messaged_at);
        }

        $response['updated_at'] = iso8601($user->updated_at);
        $response['created_at'] = iso8601($user->created_at);

        return $response;
    }
}
