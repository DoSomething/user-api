<?php

namespace App\Http\Transformers;

use App\Models\Group;
use League\Fractal\TransformerAbstract;

class GroupTransformer extends TransformerAbstract
{
    /**
     * Transform resource data.
     *
     * @param \App\Models\Group $group
     * @return array
     */
    public function transform(Group $group)
    {
        return [
            'id' => $group->id,
            'group_type_id' => $group->group_type_id,
            'name' => $group->name,
            'city' => $group->city,
            'location' => $group->location,
            'school_id' => $group->school_id,
            'goal' => $group->goal,
            'created_at' => $group->created_at->toIso8601String(),
            'updated_at' => $group->updated_at->toIso8601String(),
            'cursor' => $group->getCursor(),
        ];
    }
}
