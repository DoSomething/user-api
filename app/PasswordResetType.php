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
     * Message body for the Forgot Password type.
     *
     * @var array
     */
    public static $forgotPasswordBody = [
        'actionText' => 'Reset Password',
        'greeting' => 'Hello!',
        'introLines' => [
            'You are receiving this email because we received a password reset request for your DoSomething.org account. Here is the link to reset your password:',
        ],
        'level' => 'default',
        'outroLines' => [
            'This link will expire in 24 hours. Once you click the button above, you will be asked to reset your password on the page. If you did not request a password reset, you can ignore this email. Your password will not change and your account is safe.',
            'If you have further questions, please reach out to help@dosomething.org.',
        ],
    ];

    /**
     * Message body for the Rock The Vote Activate Account type.
     *
     * @var array
     */
    public static $rockTheVoteActivateAccountBody = [
        'actionText' => 'Set Password',
        'greeting' => 'Hello!',
        'introLines' => [
            'You are receiving this email because you need to set a password to activate your DoSomething.org account. Here is the link to set your password:',
        ],
        'level' => 'default',
        'outroLines' => [
            'This link will expire in 24 hours. Once you click the button above, you will be asked to reset your password on the page.',
            'If you have further questions, please reach out to help@dosomething.org.',
        ],
    ];

    /**
     * Returns mail body for a given password reset type.
     *
     * @param string $type
     * @return array
     */
    public static function getBody($type)
    {
        if ($type === self::$forgotPassword) {
            return self::$forgotPasswordBody;
        }

        if ($type === self::$rockTheVoteActivateAccount) {
            return self::$rockTheVoteActivateAccountBody;
        }

        return [];
    }

    /**
     * Returns mail subject for a given password reset type.
     *
     * @param string $type
     * @return array
     */
    public static function getSubject($type)
    {
        if ($type === self::$forgotPassword) {
            return 'Reset Password';
        }

        if ($type === self::$rockTheVoteActivateAccount) {
            return 'Activate your DoSomething.org Account';
        }

        return [];
    }
}
