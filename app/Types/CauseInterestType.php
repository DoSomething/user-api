<?php

namespace App\Types;

class BadgeType extends Type
{
    private const SIGNUP_BADGE = 'signup_badge';
    private const ONE_POST_BADGE = 'one_post_Badge';
    private const TWO_POSTS_BADGE = 'two_posts_badge';
    private const THREE_POSTS_BADGE = 'three_post_badge';
    private const BREAKDOWN_BADGE = 'breakdown_badge';
    private const ONE_STAFF_FAVE_BADGE = 'one_staff_fave_badge';
    private const TWO_STAFF_FAVES_BADGE = 'two_staff_faves_badge';
    private const THREE_STAFF_FAVES_BADGE = 'three_staff_faves_badge';

    /**
     * Returns labeled list of values.
     *
     * @return array
     */
    public static function labels()
    {
        return [
            'signup_badge' => 'Signup Badge',
            'one_post_Badge' => 'One Post Badge',
            'two_posts_badge' => 'Two Post Badge',
            'three_post_badge' => 'Three Post Badge',
            'breakdown_badge' => 'Breakdown Badge',
            'one_staff_fave_badge' => 'One Staff Fave Badge',
            'two_staff_fave_badge' => 'Two Staff Faves Badge',
            'three_staff_faves_badge' => 'Three Staff Faves Badge',
        ];
    }
}
