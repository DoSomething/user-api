<?php

namespace App\Http\Transformers;

use App\Models\Campaign;
use League\Fractal\TransformerAbstract;

class CampaignTransformer extends TransformerAbstract
{
    /**
     * Transform resource data.
     *
     * @param \App\Models\Campaign $campaign
     * @return array
     */
    public function transform(Campaign $campaign)
    {
        return [
            'id' => $campaign->id,
            'contentful_campaign_id' => $campaign->contentful_campaign_id,
            'internal_title' => $campaign->internal_title,
            'start_date' => $campaign->start_date->toIso8601String(),
            'end_date' => optional($campaign->end_date)->toIso8601String(),
            'is_open' => $campaign->isOpen(),
            'impact_doc' => $campaign->impact_doc,
            'cause' => $campaign->cause,
            'accepted_count' => $campaign->accepted_count,
            'pending_count' => is_staff_user()
                ? $campaign->pending_count
                : null,
            'cause_names' => $campaign->getCauseNames(),
            'group_type_id' => $campaign->group_type_id,
            'created_at' => $campaign->created_at->toIso8601String(),
            'updated_at' => $campaign->updated_at->toIso8601String(),
            'cursor' => $campaign->getCursor(),
        ];
    }
}
