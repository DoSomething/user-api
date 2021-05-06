<?php

namespace App\Http\Transformers;

use App\Models\Action;
use App\Types\ActionType;
use App\Types\PostType;
use App\Types\TimeCommitment;
use League\Fractal\TransformerAbstract;

class ActionTransformer extends TransformerAbstract
{
    /**
     * Transform resource data.
     *
     * @param \App\Models\Action $action
     * @return array
     */
    public function transform(Action $action)
    {
        return [
            'id' => $action->id,
            'name' => $action->name,
            'campaign_id' => $action->campaign_id,
            'post_type' => $action->post_type,
            'post_label' => PostType::label($action->post_type),
            'action_type' => $action->action_type,
            'action_label' => ActionType::label($action->action_type),
            'time_commitment' => $action->time_commitment,
            'time_commitment_label' => TimeCommitment::label(
                $action->time_commitment,
            ),
            'callpower_campaign_id' => $action->callpower_campaign_id,
            'reportback' => $action->reportback,
            'civic_action' => $action->civic_action,
            'scholarship_entry' => $action->scholarship_entry,
            'collect_school_id' => $action->collect_school_id,
            'volunteer_credit' => $action->volunteer_credit,
            'anonymous' => $action->anonymous,
            'online' => $action->online,
            'quiz' => $action->quiz,
            'impact_goal' => $action->impact_goal,
            'noun' => $action->noun,
            'verb' => $action->verb,
            'created_at' => $action->created_at->toIso8601String(),
            'updated_at' => $action->updated_at->toIso8601String(),
        ];
    }
}
