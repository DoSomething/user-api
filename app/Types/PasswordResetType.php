<?php

namespace App\Types;

class PasswordResetType extends Type
{
    private const FORGOT_PASSWORD = 'forgot-password';
    private const BOOST_ACTIVATE_ACCOUNT = 'boost-activate-account';
    private const BREAKDOWN_ACTIVATE_ACCOUNT = 'breakdown-activate-account';
    private const PAYS_TO_DO_GOOD_ACTIVATE_ACCOUNT = 'pays-to-do-good-activate-account';
    private const ROCK_THE_VOTE_ACTIVATE_ACCOUNT = 'rock-the-vote-activate-account';
    private const WYD_ACTIVATE_ACCOUNT = 'wyd-activate-account';

    /**
     * Returns labeled list of values.
     *
     * @return array
     */
    public static function labels()
    {
        return [
            self::FORGOT_PASSWORD => 'Forgot Password',
            self::ROCK_THE_VOTE_ACTIVATE_ACCOUNT => 'Rock The Vote Activate Account',
            self::BOOST_ACTIVATE_ACCOUNT => 'Boost Activate Account',
            self::BREAKDOWN_ACTIVATE_ACCOUNT => 'Breakdown Activate Account',
            self::PAYS_TO_DO_GOOD_ACTIVATE_ACCOUNT => 'Pays To Do Good Activate Account',
            self::WYD_ACTIVATE_ACCOUNT => 'WYD Activate Account',
        ];
    }
}
