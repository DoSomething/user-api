<?php

namespace Northstar\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * The User model. (Fight for the user!)
 *
 * @property string $_id - The MongoDB ObjectID
 * @property string $id - Aliased to _id by laravel-mongodb
 * @property string $email
 * @property string $mobile
 * @property string $password
 * @property string $drupal_password - Hashed password imported from Phoenix
 * @property string $first_name
 * @property string $last_name
 * @property string $birthdate
 * @property string $photo
 * @property array  $interests
 * @property string $source
 *
 * @property string $addr_street1
 * @property string $addr_street2
 * @property string $addr_city
 * @property string $addr_state
 * @property string $addr_zip
 * @property string $country
 * @property string $language
 *
 * We also collect a bunch of fields from Niche.com users:
 * @property string $race
 * @property string $religion
 * @property string $school_id
 * @property string $college_name
 * @property string $degree_type
 * @property string $major_name
 * @property string $hs_gradyear
 * @property string $hs_name
 * @property int $sat_math
 * @property int $sat_verbal
 * @property int $sat_writing
 *
 * And we store some external service IDs for hooking things together:
 * @property string $mobilecommons_id
 * @property string $mobilecommons_status
 * @property string $cgg_id
 * @property string $drupal_id
 * @property string $agg_id
 * @property array  $parse_installation_ids
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'mobile', 'password', 'drupal_password',

        'first_name', 'last_name', 'birthdate', 'photo', 'interests',
        'race', 'religion',

        'school_id', 'college_name', 'degree_type', 'major_name', 'hs_gradyear', 'hs_name',
        'sat_math', 'sat_verbal', 'sat_writing',

        'addr_street1', 'addr_street2', 'addr_city', 'addr_state', 'addr_zip',
        'country', 'language',

        'mobilecommons_id', 'mobilecommons_status', 'cgg_id', 'drupal_id', 'agg_id', 'source',

        'parse_installation_ids',
    ];

    /**
     * Attributes that can be queried as unique identifiers.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $indexes = [
        '_id', 'drupal_id', 'email', 'mobile',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['drupal_password', 'password'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'cgg_id' => 'integer',
    ];

    /**
     * The attributes which should be stored as MongoDate objects.
     * @see https://github.com/jenssegers/laravel-mongodb#dates
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Computed last initial field, for public profiles.
     * @return string
     */
    public function getLastInitialAttribute()
    {
        $initial = substr($this->last_name, 0, 1);

        return strtoupper($initial);
    }

    /**
     * Mutator to normalize email addresses to lowercase.
     *
     * @param string $value
     */
    public function setEmailAttribute($value)
    {
        // Drop field if attribute is empty string or null.
        if (empty($value)) {
            $this->drop('email');

            return;
        }

        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Mutator to add interests to the user's interests array, either by
     * passing an array or a comma-separated list of values.
     *
     * @param string|array $value
     */
    public function setInterestsAttribute($value)
    {
        $interests = is_array($value) ? $value : array_map('trim', explode(',', $value));

        $this->push('interests', $interests, true);
    }

    /**
     * Mutator to strip non-numeric characters from mobile numbers.
     *
     * @param string $value
     */
    public function setMobileAttribute($value)
    {
        // Drop field if attribute is empty string or null.
        if (empty($value)) {
            $this->drop('mobile');

            return;
        }

        // Otherwise, remove all non-numeric characters.
        $this->attributes['mobile'] = preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Mutator to make the `source` field immutable (e.g. once a user has been assigned
     * a source, that value cannot be changed). This allows applications to always
     * pass their own identifier in the user's source, without overwriting that value
     * for existing users.
     */
    public function setSourceAttribute($value)
    {
        if (empty($this->attributes['source'])) {
            $this->attributes['source'] = $value;
        }
    }

    /**
     * Mutator to add new Parse IDs to the user's installation IDs array,
     * either by passing an array or a comma-separated list of values.
     */
    public function setParseInstallationIdsAttribute($value)
    {
        $ids = is_array($value) ? $value : array_map('trim', explode(',', $value));

        $this->push('parse_installation_ids', $ids, true);
    }

    /**
     * Mutator to remove any existing password if we migrate a hashed password.
     * This is a one-time thing for syncing users from Phoenix and ensuring that
     * we *only* keep their latest hashed Drupal password.
     */
    public function setDrupalPasswordAttribute($value)
    {
        if (isset($this->password)) {
            $this->drop('password');
        }

        // The Drupal password is already hashed, don't do it again!
        $this->attributes['drupal_password'] = $value;
    }

    /**
     * Mutator to automatically hash any value saved to the password field,
     * and remove the hashed Drupal password if one exists.
     */
    public function setPasswordAttribute($value)
    {
        if (isset($this->drupal_password)) {
            $this->drop('drupal_password');
        }

        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * Generate a token to authenticate a user
     *
     * @return mixed
     */
    public function login()
    {
        $token = new Token();
        $token->user_id = $this->_id;
        $token->save();

        return $token;
    }

    /**
     * Does this user have a password set?
     *
     * @return bool
     */
    public function hasPassword()
    {
        return ! (empty($this->password) && empty($this->drupal_password));
    }

    /**
     * Get the corresponding Drupal ID for the given Northstar ID,
     * if it exists.
     *
     * @param $northstar_id
     * @return string|null
     */
    public static function drupalIDForNorthstarId($northstar_id)
    {
        $user = self::find($northstar_id);

        if ($user) {
            if (is_array($northstar_id)) {
                return array_column($user->toArray(), 'drupal_id');
            }

            return $user->drupal_id;
        }

        // If user doesn't exist, return null.
        return null;
    }

    /**
     * Scope a query to get all of the users in a group.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGroup($query, $id)
    {
        // Get signup group.
        return $query->where('campaigns', 'elemMatch', ['signup_id' => $id])
            ->orWhere('campaigns', 'elemMatch', ['signup_group' => $id])->get();
    }
}
