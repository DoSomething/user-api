<?php

namespace Northstar\Merge;

class Merger
{
    public function __construct()
    {
    }

    public function merge($field, $target, $duplicate)
    {
        $mergeMethod = 'merge' . studly_case($field);

        if (! method_exists($this, $mergeMethod)) {
            // throw error here
            return false;
        }

        return $this->{$mergeMethod}($target, $duplicate);
    }

    public function mergeLastAuthenticatedAt($target, $duplicate)
    {
        $targetValue = $target->last_authenticated_at;
        $duplicateValue = $duplicate->last_authenticated_at;

        return $targetValue > $duplicateValue ? $targetValue : $duplicateValue;
    }

    public function mergeLastMessagedAt($target, $duplicate)
    {
        $targetValue = $target->last_messaged_at;
        $duplicateValue = $duplicate->last_messaged_at;

        return $targetValue > $duplicateValue ? $targetValue : $duplicateValue;
    }
}
