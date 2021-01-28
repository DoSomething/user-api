<?php

namespace App\Types;

class PostType extends Type
{
    private const TEXT = 'text';
    private const PHOTO = 'photo';
    private const VOTER_REG = 'voter-reg';
    private const SHARE_SOCIAL = 'share-social';
    private const PHONE_CALL = 'phone-call';
    private const EMAIL = 'email';

    /**
     * Returns labeled list of values.
     *
     * @return array
     */
    public static function labels()
    {
        return [
            self::TEXT => 'Text Post',
            self::PHOTO => 'Photo Post',
            self::VOTER_REG => 'Voter Registration',
            self::SHARE_SOCIAL => 'Social Share',
            self::PHONE_CALL => 'Phone Call (CallPower)',
            self::EMAIL => 'Email (SoftEdge)',
        ];
    }
}
