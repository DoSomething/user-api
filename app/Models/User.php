<?php

namespace Northstar\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as ResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Northstar\Auth\Role;

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
 * @property Carbon $birthdate
 * @property string $photo
 * @property array  $interests
 * @property string $source
 * @property string $source_detail
 * @property string $role - The user's role, e.g. 'user', 'staff', or 'admin'
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
 * @property string $facebook_id
 * @property string $slack_id
 *
 * @property Carbon $last_accessed_at - The timestamp of the user's last token refresh
 * @property Carbon $last_authenticated_at - The timestamp of the user's last successful login
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, ResetPasswordContract
{
    use Authenticatable, Authorizable, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // Unique identifiers & role:
        'email', 'mobile', 'password', 'role',

        // Profile:
        'first_name', 'last_name', 'birthdate', 'photo', 'interests',

        // @TODO: Remove these? We get these from Niche but don't use anywhere.
        'school_id', 'college_name', 'degree_type', 'major_name', 'hs_gradyear', 'hs_name',
        'sat_math', 'sat_verbal', 'sat_writing', 'race', 'religion',

        // Address:
        'addr_street1', 'addr_street2', 'addr_city', 'addr_state', 'addr_zip',
        'country', 'language',

        // External profiles:
        'mobilecommons_id', 'mobilecommons_status', 'facebook_id', 'slack_id',
    ];

    /**
     * These fields are reserved for "internal" use only, and should not be
     * editable directly by end-users (e.g. from the profile endpoint).
     *
     * @var array
     */
    public static $internal = [
        'mobilecommons_id', 'mobilecommons_status', 'cgg_id', 'drupal_id', 'agg_id', 'role', 'facebook_id', 'slack_id',
    ];

    /**
     * Attributes that can be queried as unique identifiers.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $uniqueIndexes = [
        '_id', 'drupal_id', 'email', 'mobile', 'facebook_id',
    ];

    /**
     * Attributes that can be queried when filtering.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $indexes = [
        '_id', 'drupal_id', 'email', 'mobile', 'source', 'role', 'facebook_id',
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
        'birthdate' => 'date',
        'last_accessed_at' => 'datetime',
        'last_authenticated_at' => 'datetime',
    ];

    /**
     * Computed last initial field, for public profiles.
     *
     * @return string
     */
    public function getLastInitialAttribute()
    {
        $initial = Str::substr($this->last_name, 0, 1);

        return strtoupper($initial);
    }

    /**
     * Mutator to normalize email addresses to lowercase.
     *
     * @param string $value
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = normalize('email', $value);
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
        $this->attributes['mobile'] = normalize('mobile', $value);
    }

    /**
     * Accessor for the `role` field.
     *
     * @return string
     */
    public function getRoleAttribute()
    {
        return ! empty($this->attributes['role']) ? $this->attributes['role'] : 'user';
    }

    /**
     * Mutator for the `role` field.
     *
     * @param string $value
     */
    public function setRoleAttribute($value)
    {
        if (! Role::validateRole($value)) {
            return;
        }

        $this->attributes['role'] = $value;
    }

    /**
     * Accessor for the `country` field.
     *
     * @return string
     */
    public function getCountryAttribute()
    {
        if (empty($this->attributes['country'])) {
            return null;
        }

        $countryCode = Str::upper($this->attributes['country']);
        $isValid = get_countries()->has($countryCode);

        return $isValid ? $countryCode : null;
    }

    /**
     * Mutator for the `country` field.
     *
     * @param $value
     */
    public function setCountryAttribute($value)
    {
        $this->attributes['country'] = Str::upper($value);
    }

    /**
     * Mutator to add new Parse IDs to the user's installation IDs array,
     * either by passing an array or a comma-separated list of values.
     *
     * @param array|string $value
     */
    public function setParseInstallationIdsAttribute($value)
    {
        $ids = is_array($value) ? $value : array_map('trim', explode(',', $value));

        $this->push('parse_installation_ids', $ids, true);
    }

    /**
     * Mutator to automatically hash any value saved to the password field,
     * and remove the hashed Drupal password if one exists.
     *
     * @param string $value
     */
    public function setPasswordAttribute($value)
    {
        if (isset($this->drupal_password)) {
            $this->drupal_password = null;
        }

        if (! empty($this->attributes['password'])) {
            logger('Saving a new password for '.$this->id.' via '.client_id());
        }

        // Only hash and set password if not empty.
        $this->attributes['password'] = $value ? bcrypt($value) : null;
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
     * Get the display name for the user.
     *
     * @return string
     */
    public function displayName()
    {
        if (! empty($this->first_name) && ! empty($this->last_name)) {
            return $this->first_name.' '.$this->last_initial;
        } elseif (! empty($this->first_name)) {
            return $this->first_name;
        }

        return 'a doer';
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
     * Return indexes for the model.
     *
     * @return array
     */
    public function indexes()
    {
        return array_only($this->toArray(), static::$uniqueIndexes);
    }

    /**
     * Transform the user model for Blink.
     *
     * @return array
     */
    public function toBlinkPayload()
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'mobile_status' => $this->mobilecommons_status,
            'facebook_id' => $this->facebook_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'birthdate' => format_date($this->birthdate, 'Y-m-d'),
            'addr_city' => $this->addr_city,
            'addr_state' => $this->addr_state,
            'addr_zip' => $this->addr_zip,
            'language' => $this->language,
            'country' => $this->country,
            'source' => $this->source,
            'source_detail' => $this->source_detail,
            'last_authenticated_at' => $this->last_authenticated_at ? $this->last_authenticated_at->toIso8601String() : null,
            'updated_at' => $this->updated_at->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
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

    /**
     * Fill & save the user with the given array of fields.
     * Filter out any fields that have a null value.
     *
     * @param  array $fields
     */
    public function fillUnlessNull($fields)
    {
        $this->fill(array_filter($fields));
    }
}
