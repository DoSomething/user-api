<?php

namespace App\Http\Transformers;

use App\Models\Event;
use League\Fractal\TransformerAbstract;

class EventTransformer extends TransformerAbstract
{
    /**
     * Transform resource data.
     *
     * @param \App\Models\Event $event
     * @return array
     */
    public function transform(Event $event)
    {
        // Content column in events table is inconsistent.
        // Normalized this column in response.
        // @TODO remove this once events table is refactored and data is consistent.
        $content = $event->content;

        if (is_string($content)) {
            $trimmedString = substr($content, 1, -1);

            $array = explode(',', $trimmedString);
            $content = [];

            foreach ($array as $key => $value) {
                $keyValues = explode(':', $value);

                $key = trim($keyValues[0], '"');
                $value = isset($keyValues[1]) ? trim($keyValues[1], '"') : '';
                $content[$key] = $value;
            }

            // Cast values that should be integers to integers.
            $integerValues = [
                'id',
                'campaign_id',
                'campaign_run_id',
                'quantity',
                'quantity_pending',
            ];

            foreach ($integerValues as $integerValue) {
                if (
                    isset($content[$integerValue]) &&
                    $content[$integerValue] != 'null'
                ) {
                    $content[$integerValue] = (int) $content[$integerValue];
                } elseif (
                    isset($content[$integerValue]) &&
                    $content[$integerValue] === 'null'
                ) {
                    // Return as null instead of a string.
                    $content[$integerValue] = null;
                }
            }
        }

        return [
            'event_id' => $event->id,
            'eventable_id' => $event->eventable_id,
            'event_type' => $event->eventable_type,
            'content' => $content,
            'user' => $event->user,
            'created_at' => $event->created_at->toIso8601String(),
            'updated_at' => $event->updated_at->toIso8601String(),
        ];
    }
}
