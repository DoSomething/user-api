<?php

namespace App\Types;

use Illuminate\Support\Str;

class PasswordResetType extends Type
{
    // Suffix used to indicate a type is an activate account type of password reset.
    private const ACTIVATE_ACCOUNT_KEY = 'activate-account';

    // Password reset types
    private const FORGOT_PASSWORD = 'forgot-password';
    private const BOOST_ACTIVATE_ACCOUNT = 'boost-' . self::ACTIVATE_ACCOUNT_KEY;
    private const BREAKDOWN_ACTIVATE_ACCOUNT = 'breakdown-' . self::ACTIVATE_ACCOUNT_KEY;
    private const PAYS_TO_DO_GOOD_ACTIVATE_ACCOUNT = 'pays-to-do-good-' . self::ACTIVATE_ACCOUNT_KEY;
    private const ROCK_THE_VOTE_ACTIVATE_ACCOUNT = 'rock-the-vote-' . self::ACTIVATE_ACCOUNT_KEY;
    private const WYD_ACTIVATE_ACCOUNT = 'wyd-' . self::ACTIVATE_ACCOUNT_KEY;

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

    /**
     * Returns whether a password reset type value the contains activate account key.
     *
     * @param string $passwordResetTypeValue
     * @return bool
     */
    public static function isActivateAccount(string $passwordResetTypeValue)
    {
        return Str::contains($passwordResetTypeValue, self::ACTIVATE_ACCOUNT_KEY);
    }
}
