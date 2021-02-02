<?php

namespace App\Repositories;

use App\Models\Signup;

class SignupRepository
{
    /**
     * Create a signup.
     *
     * @param  array $data
     * @param  string $northstarId
     * @param  int $campaignId
     * @return \App\Models\Signup|null
     */
    public function create($data, $northstarId, $campaignId)
    {
        // Create the signup
        $signup = new Signup();

        $signup->northstar_id = $northstarId;
        $signup->campaign_id = $campaignId;
        $signup->why_participated = isset($data['why_participated'])
            ? $data['why_participated']
            : null;
        $signup->source = isset($data['source'])
            ? $data['source']
            : client_id();
        $signup->source_details = isset($data['source_details'])
            ? $data['source_details']
            : null;
        $signup->details = isset($data['details']) ? $data['details'] : null;
        $signup->referrer_user_id = isset($data['referrer_user_id'])
            ? $data['referrer_user_id']
            : null;
        $signup->group_id = isset($data['group_id']) ? $data['group_id'] : null;
        $signup->save();

        return $signup;
    }

    /**
     * Update a signup.
     *
     * @param array $data
     * @param array $data
     * @return \App\Models\Signup
     */
    public function update($signup, $data)
    {
        $signup->update($data);

        return $signup;
    }

    /**
     * Get a signup based on unique fields.
     *
     * @param  string $northstarId
     * @param  int $campaignId
     * @return \App\Models\Signup|null
     */
    public function get($northstarId, $campaignId)
    {
        $signup = Signup::where([
            'northstar_id' => $northstarId,
            'campaign_id' => $campaignId,
        ])->first();

        return $signup;
    }

    /**
     * Delete a signup.
     *
     * @param int $signupId
     * @return $post;
     */
    public function destroy($signupId)
    {
        $signup = Signup::findOrFail($signupId);

        // Soft delete the signup.
        $signup->delete();

        return $signup->trashed();
    }
}
