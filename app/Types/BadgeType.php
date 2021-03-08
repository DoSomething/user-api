<?php

namespace App\Types;

class BadgeType extends Type
{
    private const SIGNUP = 'signup';
    private const ONE_POST = 'one-post';
    private const TWO_POSTS = 'two-posts';
    private const THREE_POSTS = 'three-posts';
    private const BREAKDOWN = 'breakdown';
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
            'signup' => 'Signup Badge',
            'one_post' => 'One Post Badge',
            'two_posts' => 'Two Posts Badge',
            'three_posts' => 'Three Posts Badge',
            'breakdown' => 'Breakdown Badge',
            'one_staff_fave' => 'One Staff Fave Badge',
            'two_staff_faves' => 'Two Staff Faves Badge',
            'three_staff_faves' => 'Three Staff Faves Badge',
        ];
    }
}
