<?php

namespace App\Types;

class ActionType extends Type
{
    private const ATTEND_EVENT = 'attend-event';
    private const COLLECT_SOMETHING = 'collect-something';
    private const CONTACT_DECISIONMAKER = 'contact-decisionmaker';
    private const DONATE_SOMETHING = 'donate-something';
    private const FLAG_CONTENT = 'flag-content';
    private const HAVE_CONVERSATION = 'have-a-conversation';
    private const HOST_EVENT = 'host-event';
    private const MAKE_SOMETHING = 'make-something';
    private const SHARE_SOMETHING = 'share-something';
    private const SIGN_PETITION = 'sign-petition';
    private const OTHER = 'other';

    /**
     * Returns labeled list of values.
     *
     * @return array
     */
    public static function labels()
    {
        return [
            self::ATTEND_EVENT => 'Attend an Event',
            self::COLLECT_SOMETHING => 'Collect Something',
            self::CONTACT_DECISIONMAKER => 'Contact a Decision-Maker',
            self::DONATE_SOMETHING => 'Donate Something',
            self::FLAG_CONTENT => 'Flag Content',
            self::HAVE_CONVERSATION => 'Have a Conversation',
            self::HOST_EVENT => 'Host an Event',
            self::MAKE_SOMETHING => 'Make Something',
            self::SHARE_SOMETHING => 'Share Something',
            self::SIGN_PETITION => 'Sign a Petition',
            self::OTHER => 'Other',
        ];
    }
}
