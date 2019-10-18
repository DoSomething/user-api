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
     * A Boost subscriber Activate Account type.
     *
     * @var string
     */
    public static $boostActivateAccount = 'boost-activate-account';

    /**
     * A Breakdown subscriber Activate Account type.
     *
     * @var string
     */
    public static $breakdownActivateAccount = 'breakdown-activate-account';

    /**
     * A Pays To Do Good subscriber Activate Account type.
     *
     * @var string
     */
    public static $paysToDoGoodActivateAccount = 'pays-to-do-good-activate-account';

    /**
     * A Rock The Vote registration Activate Account type.
     *
     * @var string
     */
    public static $rockTheVoteActivateAccount = 'rock-the-vote-activate-account';

    /**
     * A WYD subscriber Activate Account type.
     *
     * @var string
     */
    public static $wydActivateAccount = 'wyd-activate-account';

    /**
     * Returns list of all valid Password Reset Types.
     *
     * @return array
     */
    public static function all()
    {
        return [
            self::$forgotPassword,
            self::$boostActivateAccount,
            self::$breakdownActivateAccount,
            self::$paysToDoGoodActivateAccount,
            self::$rockTheVoteActivateAccount,
            self::$wydActivateAccount,
        ];
    }
}
