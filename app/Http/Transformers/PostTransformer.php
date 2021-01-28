<?php

namespace App\Http\Transformers;

use App\Models\Post;
use Gate;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['action_details'];

    /**
     * List of resources possible to include.
     *
     * @var array
     */
    protected $availableIncludes = ['signup', 'siblings'];

    /**
     * Transform resource data.
     *
     * @param \App\Models\Post $post
     * @return array
     */
    public function transform(Post $post)
    {
        $response = [
            'id' => $post->id,
            'signup_id' => $post->signup_id,
            'type' => $post->type,
            'action' => $post->getActionName(),
            'action_id' => $post->action_id,
            'campaign_id' => $post->campaign_id,
            'media' => [
                'url' => $post->getMediaUrl(),
                'original_image_url' => $post->getOriginalUrl(),
                'text' => $post->text,
            ],
            'quantity' => $post->quantity,
            'hours_spent' => $post->hours_spent,
            'reactions' => [
                'reacted' => !empty($post->reaction),
                'total' => $post->reactions_count,
            ],
            'status' => $post->status,
            'location' => $post->location,
            'location_name' => $post->location_name,
            'created_at' => $post->created_at->toIso8601String(),
            'updated_at' => $post->updated_at->toIso8601String(),
            'cursor' => $post->getCursor(),
        ];

        // If this post is for an anonymous action (and viewer is not owner/staff), hide the user ID.
        if (!$post->actionModel->anonymous || Gate::allows('viewAll', $post)) {
            $response['northstar_id'] = $post->northstar_id;
        }

        if (Gate::allows('viewAll', $post)) {
            $response['tags'] = $post->tagSlugs();
            $response['source'] = $post->source;
            $response['source_details'] = $post->source_details;
            $response['remote_addr'] = '0.0.0.0';
            $response['details'] = $post->details;
            $response['referrer_user_id'] = $post->referrer_user_id;
            $response['group_id'] = $post->group_id;
            $response['school_id'] = $post->school_id;
            $response['club_id'] = $post->club_id;
        }

        return $response;
    }

    /**
     * Include the signup.
     *
     * @param \App\Models\Post $post
     * @return \League\Fractal\Resource\Collection
     */
    public function includeSignup(Post $post)
    {
        if (!$post->signup) {
            return;
        }

        $transformer = (new SignupTransformer())->setDefaultIncludes(['user']);

        return $this->item($post->signup, $transformer);
    }

    /**
     * Include the siblings.
     *
     * @param \App\Models\Post $post
     * @return \League\Fractal\Resource\Collection
     */
    public function includeSiblings(Post $post)
    {
        return $this->collection($post->siblings, new self());
    }

    /**
     * Include the action.
     *
     * @param \App\Models\Post $post
     * @return \League\Fractal\Resource\Collection
     */
    public function includeActionDetails(Post $post)
    {
        return $this->item($post->actionModel, new ActionTransformer());
    }
}
