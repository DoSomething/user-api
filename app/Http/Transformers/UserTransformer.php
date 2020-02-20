<?php

namespace Northstar\Http\Transformers;

use Northstar\Models\User;
use Illuminate\Support\Facades\Gate;

class UserTransformer extends BaseTransformer
{
    /**
     * Resources that can be included if requested.
     *
     * @return array
     */
    public function getAvailableIncludes()
    {
        return User::$sensitive;
    }

    /**
     * Is the viewer authorized to see the given optional field?
     */
    public function authorize(User $user, $attribute)
    {
        return Gate::allows('view-full-profile', $user);
    }

    /**
     * Include the `birthdate` field.
     *
     * @return \League\Fractal\Resource\Primitive
     */
    public function includeBirthdate(User $user)
    {
        return $this->primitive(format_date($user->birthdate, 'Y-m-d'));
    }

    /**
     * Include the `mobile` field.
     *
     * @return \League\Fractal\Resource\Primitive
     */
    public function includeMobile(User $user)
    {
        // @TODO: These `v2/user` endpoints should return the standard E.164 format!
        return $this->primitive(format_legacy_mobile($user->mobile));
    }

    /**
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        $response = [
            'id' => $user->_id,
            'display_name' => $user->display_name,
            'first_name' => $user->first_name,
            'display_name' => $user->display_name,
            'last_initial' => $user->last_initial,
            'photo' => null,
        ];

        // We also allow authorized users to request additional fields (see `getAvailableIncludes` above)
        // via the `?include=...` query parameter. These will automatically be read from the corresponding
        // field on the user, or an `includeFieldName` method if defined in this transformer.
        //
        // NOTE: Optional fields are behind a feature flag & may be included by default!
        if (Gate::allows('view-full-profile', $user)) {
            $response['email_preview'] = $user->email_preview;
            $response['mobile_preview'] = $user->mobile_preview;
            $response['facebook_id'] = $user->facebook_id;

            $response['interests'] = [];
            $response['age'] = $user->age;
            $response['school_id_preview'] = $user->school_id_preview;

            $response['addr_city'] = $user->addr_city;
            $response['addr_state'] = $user->addr_state;
            $response['addr_zip'] = $user->addr_zip;

            // Signup source (e.g. cgg, mobile...)
            $response['source'] = $user->source;
            $response['source_detail'] = $user->source_detail;

            // Internal & third-party service IDs:
            $response['slack_id'] = null;

            // Email subscription status
            $response['email_subscription_status'] = (bool) $user->email_subscription_status;

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

        // SMS subscription status
        $response['sms_status'] = $user->sms_status;
        $response['sms_paused'] = (bool) $user->sms_paused;

        // Email subscription topics
        $response['email_subscription_topics'] = $user->email_subscription_topics;

        $response['role'] = $user->role;

        if (Gate::allows('view-full-profile', $user)) {
            $response['deletion_requested_at'] = iso8601($user->deletion_requested_at);
            $response['last_accessed_at'] = iso8601($user->last_accessed_at);
            $response['last_authenticated_at'] = iso8601($user->last_authenticated_at);
            $response['last_messaged_at'] = iso8601($user->last_messaged_at);
        }

        $response['updated_at'] = iso8601($user->updated_at);
        $response['created_at'] = iso8601($user->created_at);

        return $response;
    }
}
