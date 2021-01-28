<?php

namespace App\Types;

class TimeCommitment extends Type
{
    private const LESS_THAN_FIVE_MINUTES = '<0.083';
    private const LESS_THAN_THIRTY_MINUTES = '<0.5';
    private const THIRTY_MINUTES_TO_HOUR = '0.5-1.0';
    private const ONE_TO_THREE_HOURS = '1.0-3.0';
    private const THREE_PLUS_HOURS = '3.0+';

    /**
     * Returns labeled list of values.
     *
     * @return array
     */
    public static function labels()
    {
        return [
            self::LESS_THAN_FIVE_MINUTES => '< 5 minutes',
            self::LESS_THAN_THIRTY_MINUTES => '< 30 minutes',
            self::THIRTY_MINUTES_TO_HOUR => '30 minutes - 1 hour',
            self::ONE_TO_THREE_HOURS => '1 - 3 hours',
            self::THREE_PLUS_HOURS => '3+ hours',
        ];
    }
}
