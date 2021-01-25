<?php

namespace App\Http\Transformers;

use App\Models\ActionStat;
use League\Fractal\TransformerAbstract;

class ActionStatTransformer extends TransformerAbstract
{
    /**
     * Transform resource data.
     *
     * @param \App\Models\ActionStat $actionStat
     * @return array
     */
    public function transform(ActionStat $actionStat)
    {
        return [
            'id' => $actionStat->id,
            'action_id' => $actionStat->action_id,
            'school_id' => $actionStat->school_id,
            'location' => $actionStat->location,
            'impact' => $actionStat->impact,
            'created_at' => $actionStat->created_at->toIso8601String(),
            'updated_at' => $actionStat->updated_at->toIso8601String(),
            'cursor' => $actionStat->getCursor(),
        ];
    }
}
