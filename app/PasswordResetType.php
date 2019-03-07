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
     * A Rock The Vote Activate Account type.
     *
     * @var string
     */
    public static $rockTheVoteActivateAccount = 'rock-the-vote-activate-account';

    /**
     * Returns list of all valid PasswordReset types.
     *
     * @return array
     */
    public static function all()
    {
        return [self::$forgotPassword, self::$rockTheVoteActivateAccount];
    }

    /**
     * Returns vars for a given Call To Action Email type.
     *
     * @param string $type
     * @return array
     */
    public static function getVars($type)
    {
        if ($type === self::$forgotPassword) {
            return [
                'actionText' => 'Reset Password',
                'intro' => 'You are receiving this email because we received a password reset request for your DoSomething.org account. Here is the link to reset your password:',
                'outro' => 'This link will expire in 24 hours. Once you click the button above, you will be asked to reset your password on the page.<br /><br />If you have further questions, please reach out to help@dosomething.org.',
                'subject' => 'Reset Password',
            ];
        }

        if ($type === self::$rockTheVoteActivateAccount) {
            return [
                'actionText' => 'Set Password',
                'intro' => 'Hello!<br /><br />You are receiving this email because you need to set a password to activate your DoSomething.org account. Here is the link to set your password:',
                'outro' => 'This link will expire in 24 hours. Once you click the button above, you will be asked to reset your password on the page.<br /><br />If you have further questions, please reach out to help@dosomething.org.',
                'subject' => 'Activate your DoSomething.org Account',
            ];
        }

        return [];
    }
}
