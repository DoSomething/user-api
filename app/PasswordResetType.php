<?php

namespace Northstar;

class PasswordResetType
{
    /**
     * A Forgot Password type.
     *
     * @var string
     */
    public static $forgotPassword = 'forgot-password';

    /**
     * A Breakdown Subscriber Activate Account type.
     *
     * @var string
     */
    public static $breakdownActivateAccount = 'breakdown-activate-account';

    /**
     * A Rock The Vote Activate Account type.
     *
     * @var string
     */
    public static $rockTheVoteActivateAccount = 'rock-the-vote-activate-account';

    /**
     * Returns list of all valid Password Reset Types.
     *
     * @return array
     */
    public static function all()
    {
        return [
            self::$forgotPassword,
            self::$breakdownActivateAccount,
            self::$rockTheVoteActivateAccount,
        ];
    }
}
