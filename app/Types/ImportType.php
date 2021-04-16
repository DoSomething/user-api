<?php

namespace App;

class ImportType
{
    /**
     * An email subscription import.
     *
     * @var string
     */
    public static $emailSubscription = 'email-subscription';

    /**
     * A mute promotions import.
     *
     * @var string
     */
    public static $mutePromotions = 'mute-promotions';

    /**
     * A Rock The Vote import.
     *
     * @var string
     */
    public static $rockTheVote = 'rock-the-vote';

    /**
     * Returns config array of given import type.
     *
     * @return array
     */
    public static function getConfig($type)
    {
        if ($type === self::$emailSubscription) {
            return config('import.email_subscription');
        }

        if ($type === self::$rockTheVote) {
            return config('import.rock_the_vote');
        }

        return [];
    }
}
