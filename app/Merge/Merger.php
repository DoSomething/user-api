<?php

namespace Northstar\Merge;

use Northstar\Exceptions\NorthstarValidationException;

class Merger
{
    public function merge($field, $target, $duplicate)
    {
        $mergeMethod = 'merge'.studly_case($field);

        if (! method_exists($this, $mergeMethod)) {
            throw new NorthstarValidationException(['Unable to merge '.$field.' field. No merge instructions found.'], ['target' => $target, 'duplicate' => $duplicate]);
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
