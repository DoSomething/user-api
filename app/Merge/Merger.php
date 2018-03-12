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
        return $this->chooseMostRecentDate('last_authenticated_at', $target, $duplicate);
    }

    public function mergeLastMessagedAt($target, $duplicate)
    {
        return $this->chooseMostRecentDate('last_messaged_at', $target, $duplicate);
    }

    public function mergeLastAccessedAt($target, $duplicate)
    {
        return $this->chooseMostRecentDate('last_accessed_at', $target, $duplicate);
    }

    public function mergeLanguage($target, $duplicate)
    {
        if ($target->last_accessed_at > $duplicate->last_accessed_at) {
            return $target->language;
        }

        return $duplicate->language;
    }

    public function chooseMostRecentDate($field, $target, $duplicate)
    {
        $targetValue = $target->{$field};
        $duplicateValue = $duplicate->{$field};

        return $targetValue > $duplicateValue ? $targetValue : $duplicateValue;
    }
}
