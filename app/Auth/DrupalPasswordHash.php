<?php

namespace Northstar\Auth;

define('DRUPAL_MIN_HASH_COUNT', 7);
define('DRUPAL_MAX_HASH_COUNT', 30);
define('DRUPAL_HASH_LENGTH', 55);

class DrupalPasswordHash
{
    /**
     * Check if a given password matches the hash created by Drupal. For details,
     * see Drupal 7's `user_check_password` function.
     * @see https://api.drupal.org/api/drupal/includes!password.inc/function/user_check_password/7
     *
     * @param $password - Unhashed password to verify
     * @param $drupal_password - Hashed password
     *
     * @return bool - Do they match?
     */
    public static function check($password, $drupal_password)
    {
        if (substr($drupal_password, 0, 2) == 'U$') {
            // This may be an updated password from user_update_7000(). Such hashes
            // have 'U' added as the first character and need an extra md5().
            $stored_hash = substr($drupal_password, 1);
            $password = md5($password);
        } else {
            $stored_hash = $drupal_password;
        }

        $type = substr($stored_hash, 0, 3);
        switch ($type) {
            case '$S$':
                // A normal Drupal 7 password using sha512.
                $hash = self::_password_crypt(
                    'sha512',
                    $password,
                    $stored_hash,
                );
                break;
            case '$H$':
            // phpBB3 uses "$H$" for the same thing as "$P$".
            case '$P$':
                // A phpass password generated using md5.  This is an
                // imported password or from an earlier Drupal version.
                $hash = self::_password_crypt('md5', $password, $stored_hash);
                break;
            default:
                return false;
        }
        return $hash && $stored_hash == $hash;
    }

    // @see: https://api.drupal.org/api/drupal/includes%21password.inc/function/_password_crypt/7
    private static function _password_crypt($algo, $password, $setting)
    {
        // Prevent DoS attacks by refusing to hash large passwords.
        if (strlen($password) > 512) {
            return false;
        }
        // The first 12 characters of an existing hash are its setting string.
        $setting = substr($setting, 0, 12);

        if ($setting[0] != '$' || $setting[2] != '$') {
            return false;
        }
        $count_log2 = self::_password_get_count_log2($setting);
        // Hashes may be imported from elsewhere, so we allow != DRUPAL_HASH_COUNT
        if (
            $count_log2 < DRUPAL_MIN_HASH_COUNT ||
            $count_log2 > DRUPAL_MAX_HASH_COUNT
        ) {
            return false;
        }
        $salt = substr($setting, 4, 8);
        // Hashes must have an 8 character salt.
        if (strlen($salt) != 8) {
            return false;
        }

        // Convert the base 2 logarithm into an integer.
        $count = 1 << $count_log2;

        // We rely on the hash() function being available in PHP 5.2+.
        $hash = hash($algo, $salt . $password, true);
        do {
            $hash = hash($algo, $hash . $password, true);
        } while (--$count);

        $len = strlen($hash);
        $output = $setting . self::_password_base64_encode($hash, $len);
        // _password_base64_encode() of a 16 byte MD5 will always be 22 characters.
        // _password_base64_encode() of a 64 byte sha512 will always be 86 characters.
        $expected = 12 + ceil((8 * $len) / 6);
        return strlen($output) == $expected
            ? substr($output, 0, DRUPAL_HASH_LENGTH)
            : false;
    }

    // @see https://api.drupal.org/api/drupal/includes%21password.inc/function/_password_base64_encode/7
    private static function _password_base64_encode($input, $count)
    {
        $output = '';
        $i = 0;
        $itoa64 = self::_password_itoa64();
        do {
            $value = ord($input[$i++]);
            $output .= $itoa64[$value & 0x3f];
            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }
            $output .= $itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }
            $output .= $itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            $output .= $itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }

    // @see https://api.drupal.org/api/drupal/includes%21password.inc/function/_password_get_count_log2/7
    private static function _password_get_count_log2($setting)
    {
        $itoa64 = self::_password_itoa64();
        return strpos($itoa64, $setting[3]);
    }

    // @see https://api.drupal.org/api/drupal/includes%21password.inc/function/_password_itoa64/7
    private static function _password_itoa64()
    {
        return './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    }
}
