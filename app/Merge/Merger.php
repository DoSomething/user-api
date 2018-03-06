<?php

namespace Northstar\Merge;

class Merger
{
    public function __construct()
    {
    }

    public function merge($field, $target, $duplicate)
    {
        switch ($field) {
            case 'last_authenticated_at':
                return $this->mergeLastAuthenticatedAt($target, $duplicate);
            case 'last_messaged_at':
                return $this->mergeLastMessagedAt($target, $duplicate);
            default:
                return false;
        }
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
