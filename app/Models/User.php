<?php

namespace Northstar\Models;

use Carbon\Carbon;
use Email\Parse as EmailParser;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as ResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Northstar\Auth\Role;
use Northstar\PasswordResetType;
use Northstar\Jobs\SendPasswordResetToCustomerIo;

/**
 * The User model. (Fight for the user!)
 *
 * @property string $_id - The MongoDB ObjectID
 * @property string $id - Aliased to _id by laravel-mongodb
 * @property string $email
 * @property string $mobile
 * @property string e164 - temporary! will be used as new `mobile`.
 * @property string $password
 * @property string $drupal_password - Hashed password imported from Phoenix
 * @property string $first_name
 * @property string $last_name
 * @property Carbon $birthdate
 * @property string $source
 * @property string $source_detail
 * @property string $voter_registration_status
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
 * Source for the address fields (e.g. 'sms')
 * @property string $addr_source
 *
 * And we store some external service IDs for hooking things together:
 * @property string $mobilecommons_id
 * @property string $drupal_id
 * @property string $facebook_id
 *
 * Messaging subscription status:
 * @property string $sms_status
 * @property bool   $sms_paused
 * @property bool $email_subscription_status
 * @property array $email_subscription_topics
 *
 * 
 * Causes and Interests
 * @property array $causes
 *
 * Fields for Make a Voting Plan
 * @property string $voting_plan_status
 * @property string $voting_plan_method_of_transport
 * @property string $voting_plan_time_of_day
 * @property string $voting_plan_attending_with
 *
 * @property Carbon $last_accessed_at - The timestamp of the user's last token refresh
 * @property Carbon $last_authenticated_at - The timestamp of the user's last successful login
 * @property Carbon $last_messaged_at - The timestamp of the last message this user sent
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * The feature flags this user has
 * @property object $feature_flags
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, ResetPasswordContract
{
    use Authenticatable, Authorizable, Notifiable, CanResetPassword;

    /**
     * Should changes to this model's attributes be stored
     * in an audit property on the database record?
     *
     * @var bool
     */
    protected $audited = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // Unique identifiers & role:
        'email', 'mobile', 'password', 'role',

        // Profile:
        'first_name', 'last_name', 'birthdate', 'voter_registration_status', 'causes',

        // Address:
        'addr_street1', 'addr_street2', 'addr_city', 'addr_state', 'addr_zip',
        'country', 'language', 'addr_source',

        // External profiles:
        'mobilecommons_id', 'mobilecommons_status', 'facebook_id',
        'sms_status', 'sms_paused', 'email_subscription_status', 'email_subscription_topics', 'last_messaged_at',

        // Voting Plan:
        'voting_plan_status', 'voting_plan_method_of_transport', 'voting_plan_time_of_day', 'voting_plan_attending_with',

        // Feature flags:
        'feature_flags',
    ];

    /**
     * These fields are reserved for "internal" use only, and should not be
     * editable directly by end-users (e.g. from the profile endpoint).
     *
     * @var array
     */
    public static $internal = [
        'drupal_id', 'role', 'facebook_id',
        'mobilecommons_id', 'mobilecommons_status', 'sms_status', 'sms_paused',
        'last_messaged_at', 'feature_flags', 'totp',
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
     * Attributes that contain personally-indentifiable information. These
     * can be requested via the API with the `?include=` query parameter.
     *
     * @var array
     */
    public static $sensitive = [
        'email',
        'mobile',
        'last_name',
        'addr_street1',
        'addr_street2',
        'birthdate',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['drupal_password', 'password', 'audit', 'totp'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'birthdate',
        'last_accessed_at',
        'last_authenticated_at',
        'last_messaged_at',
        self::UPDATED_AT,
        self::CREATED_AT,
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'birthdate' => 'date',
        'sms_paused' => 'boolean',
        'email_subscription_status' => 'boolean',
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
     * Computed "display name" field, for public profiles,
     * e.g. "Puppet S." for "Puppet Sloth".
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if ($this->last_initial) {
            return $this->first_name.' '.$this->last_initial.'.';
        }

        return $this->first_name;
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
     * Computed "email preview" field, e.g. "dfu...@gmail.com"
     *
     * @return string
     */
    public function getEmailPreviewAttribute()
    {
        if (! $this->email) {
            return null;
        }

        $email = EmailParser::getInstance()->parse($this->email, false);

        if ($email['invalid']) {
            return '???';
        }

        // We'll show the user's email domain for common providers.
        // See: https://dsdata.looker.com/sql/kkk4zqtkwffymv
        $allowedDomains = [
            'aim.com', 'aol.com', 'att.net', 'bellsouth.net', 'comcast.net', 'cox.net', 'dosomething.org',
            'gmail.com', 'hotmail.com', 'icloud.com', 'live.com', 'me.com', 'msn.com', 'outlook.com',
            'rocketmail.com', 'sbcglobal.net', 'verizon.net', 'yahoo.com', 'ymail.com',
        ];

        $domain = $email['domain'];

        $previewedMailbox = str_limit($email['local_part'], 3);
        $previewedDomain = in_array($domain, $allowedDomains) ? $domain : str_limit($domain, 4);

        return $previewedMailbox.'@'.$previewedDomain;
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
     * Computed "mobile preview" field, e.g. "(212) 254-XXXX"
     *
     * @return string
     */
    public function getMobilePreviewAttribute()
    {
        if (! $this->mobile) {
            return null;
        }

        $mobile = parse_mobile($this->mobile);

        if (! $mobile) {
            return '(XXX) XXX-XXXX';
        }

        $formattedNumber = format_mobile($mobile, PhoneNumberFormat::NATIONAL);

        // Redact the last four digits after formatting.
        return substr($formattedNumber, 0, -4).'XXXX';
    }

    /**
     * Mutator to support old `mobilecommons_status` field input.
     *
     * @param string $value
     */
    public function setMobilecommonsStatusAttribute($value)
    {
        $this->attributes['sms_status'] = $value;
    }

    /**
     * Mutator for setting the birthdate field.
     *
     * @param string|Carbon $value
     */
    public function setBirthdateAttribute($value)
    {
        $this->setArbitraryDateString('birthdate', $value);
    }

    /**
     * Computed age field.
     *
     * @return int
     */
    public function getAgeAttribute()
    {
        return optional($this->birthdate)->diffInYears(now());
    }

    /**
     * Mutator for setting the last_messaged_at field.
     *
     * @param string|Carbon $value
     */
    public function setLastMessagedAtAttribute($value)
    {
        $this->setArbitraryDateString('last_messaged_at', $value);
    }

    /**
     * Mutator to parse non-standard date strings into MongoDates.
     *
     * @param string|Carbon $value
     */
    public function setArbitraryDateString($attribute, $value)
    {
        if (is_null($value)) {
            $this->attributes[$attribute] = null;

            return;
        }

        // Parse user-entered strings like '10/31/1990' or `October 31st 1990'.
        if (is_string($value)) {
            $value = strtotime($value);
        }

        $this->attributes[$attribute] = $this->fromDateTime($value);
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
     * WARNING: THIS PAYLOAD CAN ONLY INCLUDE 300 ATTRIBUTES!!
     *
     * @return array
     */
    public function toCustomerIoPayload()
    {
        $payload = [
            'id' => $this->id,
            'email' => $this->email,
            'mobile' => $this->mobile, // TODO: Update Blink to just accept 'phone' field.
            'sms_status' => $this->sms_status,
            'sms_status_source' => (isset($this->audit['sms_status']['source'])) ? $this->audit['sms_status']['source'] : null,
            'sms_paused' => (bool) $this->sms_paused,
            'facebook_id' => $this->facebook_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'birthdate' => optional($this->birthdate)->timestamp,
            'addr_city' => $this->addr_city,
            'addr_state' => $this->addr_state,
            'addr_zip' => $this->addr_zip,
            'language' => $this->language,
            'country' => $this->country,
            'voter_registration_status' => $this->voter_registration_status,
            'source' => $this->source,
            'source_detail' => $this->source_detail,
            'last_messaged_at' => optional($this->last_messaged_at)->timestamp,
            'last_authenticated_at' => iso8601($this->last_authenticated_at), // TODO: Update Blink to just accept timestamp.
            'updated_at' => iso8601($this->updated_at), // TODO: Update Blink to just accept timestamp.
            'created_at' => iso8601($this->created_at), // TODO: Update Blink to just accept timestamp.
            'news_email_subscription_status' => isset($this->email_subscription_topics) ? in_array('news', $this->email_subscription_topics) : false,
            'lifestyle_email_subscription_status' => isset($this->email_subscription_topics) ? in_array('lifestyle', $this->email_subscription_topics) : false,
            'community_email_subscription_status' => isset($this->email_subscription_topics) ? in_array('community', $this->email_subscription_topics) : false,
            'scholarship_email_subscription_status' => isset($this->email_subscription_topics) ? in_array('scholarships', $this->email_subscription_topics) : false,
            'animal_welfare_cause' => in_array('animal_welfare_cause', $this->causes) ? true : false,
        ];

        // Only include email subscription status if we have that information.
        if (isset($this->email_subscription_status)) {
            $payload['email_subscription_status'] = $this->email_subscription_status;
            $payload['unsubscribed'] = (! $this->email_subscription_status);
        }

        if (isset($this->feature_flags)) {
            if (array_key_exists('badges', $this->feature_flags)) {
                $payload['badges_feature_flag'] = $this->feature_flags['badges'];
            }
        }

        return $payload;
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

    /**
     * Set the source & source_detail on this user
     * if they don't already exist.
     *
     * @param string $source
     * @param string $detail
     */
    public function setSource($source, $detail = null)
    {
        if ($this->source) {
            return;
        }

        $this->source = $source ?: client_id();
        $this->source_detail = $detail;
    }

    /**
     * Returns password reset URL with given token and type.
     *
     * @param  string  $token
     * @param  string  $type
     * @return string
     */
    public function getPasswordResetUrl($token, $type)
    {
        return route('password.reset', [
            $token,
            'email' => $this->email,
            'type' => $type,
        ]);
    }

    /**
     * Overrides the default method to send a password reset email.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        return $this->sendPasswordReset($token, PasswordResetType::$forgotPassword);
    }

    /**
     * Creates a call_to_action_email Customer.io event with a password reset URL.
     *
     * @param  string  $token
     * @param  string  $type
     * @return void
     */
    public function sendPasswordReset($token, $type)
    {
        SendPasswordResetToCustomerIo::dispatch($this, $token, $type);
    }

    /**
     * Add the given topic to the user's array of topics if it is not already there.
     *
     * @param string $topic
     */
    public function addEmailSubscriptionTopic($topic)
    {
        // Add the new topic to the existing array of topics
        $this->email_subscription_topics = array_merge($this->email_subscription_topics ?: [], [$topic]);
    }

    /**
     * Mutator to ensure no duplicates in the email topics array.
     *
     * @param array $value
     */
    public function setEmailSubscriptionTopicsAttribute($value)
    {
        // Set de-duped array as email_subscription_topics
        $this->attributes['email_subscription_topics'] = array_values(array_unique($value));
    }

    public function getCausesAttribute($value)
    {
        return ! empty($value) ? $value : [];
    }

    // public function addUserInterest($interest)
    // {
    //     // Add new interest to the existing array of interests
    //     $this->causes = array_merge($this->causes ?: [], [$interest]);
    // }

    // public function setUserInterestAttribute($value)
    // {
    //     // Set de-duped array as causes
    //     $this->attributes['causes'] = array_values(array_unique($value));
    // }
}
