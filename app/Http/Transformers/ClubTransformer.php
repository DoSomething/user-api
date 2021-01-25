<?php

namespace App\Http\Transformers;

use App\Models\Club;
use League\Fractal\TransformerAbstract;

class ClubTransformer extends TransformerAbstract
{
    /**
     * Transform resource data.
     *
     * @param \App\Models\Club $club
     * @return array
     */
    public function transform(Club $club)
    {
        return [
            'id' => $club->id,
            'name' => $club->name,
            'leader_id' => $club->leader_id,
            'city' => $club->city,
            'location' => $club->location,
            'school_id' => $club->school_id,
            'created_at' => $club->created_at->toIso8601String(),
            'updated_at' => $club->updated_at->toIso8601String(),
            'cursor' => $club->getCursor(),
        ];
    }
}
