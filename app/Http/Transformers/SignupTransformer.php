<?php

namespace App\Http\Transformers;

use App\Models\Signup;
use App\Models\User;
use Gate;
use League\Fractal\TransformerAbstract;

class SignupTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include.
     *
     * @var array
     */
    protected $availableIncludes = ['posts', 'user', 'accepted_quantity'];

    /**
     * Transform resource data.
     *
     * @param \App\Models\Signup $signup
     * @return array
     */
    public function transform(Signup $signup)
    {
        $response = [
            'id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'campaign_run_id' => $signup->campaign_run_id,
            'quantity' => $signup->quantity,
            'created_at' => $signup->created_at->toIso8601String(),
            'updated_at' => $signup->updated_at->toIso8601String(),
            'cursor' => $signup->getCursor(),
        ];

        if (Gate::allows('viewAll', $signup)) {
            $response['why_participated'] = $signup->why_participated;
            $response['source'] = $signup->source;
            $response['source_details'] = $signup->source_details;
            $response['details'] = $signup->details;
            $response['referrer_user_id'] = $signup->referrer_user_id;
            $response['group_id'] = $signup->group_id;
            $response['club_id'] = $signup->club_id;
        }

        return $response;
    }

    /**
     * Include posts.
     *
     * @param \App\Models\Signup $signup
     * @return \League\Fractal\Resource\Collection
     */
    public function includePosts(Signup $signup)
    {
        // When including posts, only include posts the user should be able to see:
        return $this->collection($signup->visiblePosts, new PostTransformer());
    }

    /**
     * Include the user data (optionally).
     *
     * @param \App\Models\Signup $signup
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(Signup $signup)
    {
        return $this->item($signup->user, new UserTransformer());
    }

    /**
     * Include accepted quantity.
     *
     * @param \App\Models\Signup $signup
     * @return \League\Fractal\Resource\Collection
     */
    public function includeAcceptedQuantity(Signup $signup)
    {
        return $this->item(
            $signup->getAcceptedQuantity(),
            new AcceptedQuantityTransformer(),
        );
    }
}
