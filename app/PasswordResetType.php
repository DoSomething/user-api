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
     * Returns list of all valid Password Reset Types.
     *
     * @return array
     */
    public static function all()
    {
        return [self::$forgotPassword, self::$rockTheVoteActivateAccount];
    }

    /**
     * Returns Call To Action Email parameters for a given Password Reset Type.
     *
     * @param string $type
     * @return array
     */
    public static function getEmailVars($type)
    {
        if ($type === self::$rockTheVoteActivateAccount) {
            return [
                'actionText' =>'Set Password',
                'greeting' => 'Hello!',
                'intro' => 'You are receiving this email because you need to set a password to activate your DoSomething.org account. Here is the link to set your password:',
                'outro' => 'This link will expire in 24 hours. Once you click the button above, you will be asked to reset your password on the page.<br /><br />If you have further questions, please reach out to help@dosomething.org.',
                'subject' => 'Activate your DoSomething.org Account',
            ];
        }

        return [
            'actionText' => 'Reset Password',
            'greeting' => 'Hello!',
            'intro' => 'You are receiving this email because we received a password reset request for your DoSomething.org account. Here is the link to reset your password:',
            'outro' => 'This link will expire in 24 hours. Once you click the button above, you will be asked to reset your password on the page. If you did not request a password reset, you can ignore this email. Your password will not change and your account is safe.<br /><br />If you have further questions, please reach out to help@dosomething.org.',
            'subject' => 'Reset Password',
        ];
    }
}
