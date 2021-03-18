<?php

namespace App\Types;

class BadgeType extends Type
{
    private const SIGNUP = 'signup';
    private const ONE_POST = 'one-post';
    private const TWO_POSTS = 'two-posts';
    private const THREE_POSTS = 'three-posts';
    private const FOUR_POSTS = 'four-posts';
    private const NEWS_SUBSCRIPTION = 'news-subscription';
    private const ONE_STAFF_FAVE = 'one-staff-fave';
    private const TWO_STAFF_FAVES = 'two-staff-faves';
    private const THREE_STAFF_FAVES = 'three-staff-faves';

    /**
     * Returns labeled list of values.
     *
     * @return array
     */
    public static function labels()
    {
        return [
            self::SIGNUP => 'Signup Badge',
            self::ONE_POST => 'One Post Badge',
            self::TWO_POSTS => 'Two Posts Badge',
            self::THREE_POSTS => 'Three Posts Badge',
            self::FOUR_POSTS => 'Four Posts Badge',
            self::NEWS_SUBSCRIPTION => 'News Subscription Badge',
            self::ONE_STAFF_FAVE => 'One Staff Fave Badge',
            self::TWO_STAFF_FAVES => 'Two Staff Faves Badge',
            self::THREE_STAFF_FAVES => 'Three Staff Faves Badge',
        ];
    }
}
