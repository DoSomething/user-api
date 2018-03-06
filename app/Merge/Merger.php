<?php

namespace Northstar\Merge;

class Merger
{
    public function __construct()
    {

    }

    public function merge($field, $targetValue, $duplicateValue)
    {
        switch ($field) {
            case 'last_authenticated_at':
                return $this->mergeLastAuthenticatedAt($targetValue, $duplicateValue);
            case 'last_messaged_at':
                return $this->mergeLastMessagedAt($targetValue, $duplicateValue);
            default:
                return false;
        }
    }

    public function mergeLastAuthenticatedAt($targetValue, $duplicateValue)
    {
        return $targetValue > $duplicateValue ? $targetValue : $duplicateValue;
    }

    public function mergeLastMessagedAt($targetValue, $duplicateValue)
    {
        return $targetValue > $duplicateValue ? $targetValue : $duplicateValue;
    }
}
