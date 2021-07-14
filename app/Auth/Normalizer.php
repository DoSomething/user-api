<?php

namespace App\Auth;

use Carbon\Carbon;
use libphonenumber\PhoneNumberFormat;

class Normalizer
{
    /**
     * Normalize the given credentials in the array or request (for example, before
     * validating, or before saving to the database).
     *
     * @param \ArrayAccess|array $credentials
     * @return mixed
     */
    public function credentials($credentials)
    {
        // If a username is given, figure out whether it's an email or mobile number.
        if (!empty($credentials['username'])) {
            $type = is_email($credentials['username']) ? 'email' : 'mobile';
            $credentials[$type] = $credentials['username'];
            unset($credentials['username']);
        }

        // Map id to Mongo's _id ObjectID field.
        if (!empty($credentials['id'])) {
            $credentials['_id'] = $credentials['id'];
            unset($credentials['id']);
        }

        if (!empty($credentials['email'])) {
            $credentials['email'] = $this->email($credentials['email']);
        }

        if (!empty($credentials['mobile'])) {
            $mobile = $this->mobile($credentials['mobile']);
            $credentials['mobile'] = $mobile ?: '';
        }

        return $credentials;
    }

    /**
     * Sanitize an email address before verifying or saving to the database.
     * This method will likely be called multiple times per user, so it *must*
     * provide the same result if so.
     *
     * @param string $email
     * @return string
     */
    public function email($email)
    {
        return trim(strtolower($email));
    }

    /**
     * Sanitize a mobile number before verifying or saving to the database.
     * This method will likely be called multiple times per user, so it *must*
     * provide the same result if so.
     *
     * @param string $mobile
     * @return string
     */
    public function mobile($mobile)
    {
        if (empty($mobile)) {
            return '';
        }

        // Normalize "1 (555) 555-5555" format without leading "+".
        $digits = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($digits) === 11 && $digits[0] === '1') {
            $mobile = '+' . $mobile;
        }

        $number = parse_mobile($mobile);

        if (!$number) {
            return '';
        }

        return format_mobile($number, PhoneNumberFormat::E164);
    }

    /**
     * Parse an array of string into Carbon dates.
     *
     * @param string[] $strings
     * @return \Carbon\Carbon[]
     */
    public function dates($strings)
    {
        $dates = collect($strings)->map(function ($string) {
            return new Carbon($string);
        });

        return $dates->toArray();
    }

    /**
     * Normalizes email or mobile value.
     *
     * @param string $value
     * @return string
     */
    public function username($value)
    {
        $type = is_email($value) ? 'email' : 'mobile';

        return $this->{$type}($value);
    }
}
