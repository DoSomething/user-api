<?php

namespace App\Http\Transformers;

use App\Models\GroupType;
use League\Fractal\TransformerAbstract;

class GroupTypeTransformer extends TransformerAbstract
{
    /**
     * Transform resource data.
     *
     * @param \App\Models\GroupType $groupType
     * @return array
     */
    public function transform(GroupType $groupType)
    {
        return [
            'id' => $groupType->id,
            'name' => $groupType->name,
            'filter_by_location' => $groupType->filter_by_location,
            'created_at' => $groupType->created_at->toIso8601String(),
            'updated_at' => $groupType->updated_at->toIso8601String(),
        ];
    }
}
