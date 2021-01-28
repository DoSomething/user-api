<?php

namespace App\Http\Transformers;

use League\Fractal\TransformerAbstract;

class AcceptedQuantityTransformer extends TransformerAbstract
{
    /**
     * Transform resource data.
     *
     * @param int $quantity
     * @return array
     */
    public function transform($quantity)
    {
        return ['quantity' => $quantity];
    }
}
