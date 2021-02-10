<?php

namespace App\Models;

use App\Auth\Role;
use App\Jobs\CreateCustomerIoEvent;
use App\Jobs\SendCustomerIoEmail;
use App\Services\GraphQL;
use App\Types\PasswordResetType;
use Carbon\Carbon;
use Email\Parse as EmailParser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as ResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Jenssegers\Mongodb\Auth\DatabaseTokenRepository;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use libphonenumber\PhoneNumberFormat;

/**
 * The User model. (Fight for the user!).
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
 * @property string $referrer_user_id
 * @property string $voter_registration_status
 * @property string $school_id
 * @property string $club_id - The Rogue Club ID that the user is a part of
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
 * @property string $google_id
 *
 * Messaging subscription status:
 * @property string $sms_status
 * @property bool   $sms_paused
 * @property bool $email_subscription_status
 * @property array $email_subscription_topics
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
class User extends MongoModel implements
    AuthenticatableContract,
    AuthorizableContract,
    ResetPasswordContract
{
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        Notifiable,
        SoftDeletes;

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
        'email',
        'mobile',
        'role',

        // Profile:
        'first_name',
        'last_name',
        'birthdate',
        'voter_registration_status',
        'causes',
        'school_id',
        'club_id',

        // Address:
        'addr_street1',
        'addr_street2',
        'addr_city',
        'addr_state',
        'addr_zip',
        'country',
        'language',
        'addr_source',

        // Source info:
        'referrer_user_id',

        // External profiles:
        'mobilecommons_id',
        'mobilecommons_status',
        'facebook_id',
        'google_id',

        // SMS Subscription:
        'sms_status',
        'sms_paused',
        'sms_subscription_topics',
        'last_messaged_at',

        // Email Subscription:
        'email_subscription_status',
        'email_subscription_topics',

        // Voting Method/Plan fields:
        'voting_method',
        'voting_plan_attending_with',
        'voting_plan_status',
        'voting_plan_method_of_transport',
        'voting_plan_time_of_day',

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
        'drupal_id',
        'role',
        'facebook_id',
        'google_id',
        'mobilecommons_id',
        'mobilecommons_status',
        'sms_status',
        'sms_paused',
        'last_messaged_at',
        'feature_flags',
        'totp',
        'referrer_user_id',
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
        '_id',
        'drupal_id',
        'email',
        'mobile',
        'facebook_id',
        'google_id',
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
        '_id',
        'drupal_id',
        'email',
        'mobile',
        'source',
        'role',
        'facebook_id',
        'google_id',
        'club_id',
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
        'school_id',
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
        'deletion_requested_at',
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
            return $this->first_name . ' ' . $this->last_initial . '.';
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
     * Computed "email preview" field, e.g. "dfu...@gmail.com".
     *
     * @return string
     */
    public function getEmailPreviewAttribute()
    {
        if (!$this->email) {
            return null;
        }

        $email = EmailParser::getInstance()->parse($this->email, false);

        if ($email['invalid']) {
            return '???';
        }

        // We'll show the user's email domain for common providers.
        // See: https://dsdata.looker.com/sql/kkk4zqtkwffymv
        $allowedDomains = [
            'aim.com',
            'aol.com',
            'att.net',
            'bellsouth.net',
            'comcast.net',
            'cox.net',
            'dosomething.org',
            'gmail.com',
            'hotmail.com',
            'icloud.com',
            'live.com',
            'me.com',
            'msn.com',
            'outlook.com',
            'rocketmail.com',
            'sbcglobal.net',
            'verizon.net',
            'yahoo.com',
            'ymail.com',
        ];

        $domain = $email['domain'];

        $previewedMailbox = Str::limit($email['local_part'], 3);
        $previewedDomain = in_array($domain, $allowedDomains)
            ? $domain
            : Str::limit($domain, 4);

        return $previewedMailbox . '@' . $previewedDomain;
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
     * Computed "mobile preview" field, e.g. "(212) 254-XXXX".
     *
     * @return string
     */
    public function getMobilePreviewAttribute()
    {
        if (!$this->mobile) {
            return null;
        }

        $mobile = parse_mobile($this->mobile);

        if (!$mobile) {
            return '(XXX) XXX-XXXX';
        }

        $formattedNumber = format_mobile($mobile, PhoneNumberFormat::NATIONAL);

        // Redact the last four digits after formatting.
        return substr($formattedNumber, 0, -4) . 'XXXX';
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
        return !empty($this->attributes['role'])
            ? $this->attributes['role']
            : 'user';
    }

    /**
     * Mutator for the `role` field.
     *
     * @param string $value
     */
    public function setRoleAttribute($value)
    {
        if (!Role::validateRole($value)) {
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

        if (!empty($this->attributes['password'])) {
            logger(
                'Saving a new password for ' .
                    $this->id .
                    ' via ' .
                    client_id(),
            );
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
        return !(empty($this->password) && empty($this->drupal_password));
    }

    /**
     * Check to see if this user matches one of the given roles.
     *
     * @param  array|mixed $roles - role(s) to check
     * @return bool
     */
    public function hasRole($roles)
    {
        // Prepare an array of roles to check.
        // e.g. $user->hasRole('admin') => ['admin']
        //      $user->hasRole('admin, 'staff') => ['admin', 'staff']
        $roles = is_array($roles) ? $roles : func_get_args();

        return in_array($this->role, $roles);
    }

    /**
     * Get the display name for the user.
     *
     * @return string
     */
    public function displayName()
    {
        if (!empty($this->first_name) && !empty($this->last_name)) {
            return $this->first_name . ' ' . $this->last_initial;
        } elseif (!empty($this->first_name)) {
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
        return Arr::only($this->toArray(), static::$uniqueIndexes);
    }

    /**
     * Transform the user model for Customer.io's profile schema.
     *
     * @return array
     */
    public function toCustomerIoPayload()
    {
        // Please keep in mind the following restrictions when adding fields:
        //
        //  - These values may only be strings or integers (no objects or arrays).
        //  - Any user-provided strings must be sanitized by 'strip_tags'.
        //  - All dates should be formatted as UNIX timestamps.
        //
        // (Note: this payload is limited to 300 attributes).
        $payload = [
            'id' => $this->id,
            'email' => $this->email,
            'phone' => $this->mobile,
            'sms_status' => $this->sms_status,
            'sms_status_source' => data_get($this->audit, 'sms_status.source'),
            'sms_paused' => (bool) $this->sms_paused,
            'facebook_id' => $this->facebook_id,
            'google_id' => $this->google_id,
            'first_name' => strip_tags($this->first_name),
            'display_name' => strip_tags($this->display_name),
            'last_name' => null, // We want to unset this on Customer.io profiles.
            'birthdate' => optional($this->birthdate)->timestamp,
            'addr_city' => strip_tags($this->addr_city),
            'addr_state' => strip_tags($this->addr_state),
            'addr_zip' => strip_tags($this->addr_zip),
            'language' => $this->language,
            'country' => $this->country,
            'school_id' => $this->school_id,
            'club_id' => $this->club_id,
            'voter_registration_status' => $this->voter_registration_status,
            'source' => $this->source,
            'source_detail' => $this->source_detail,
            'referrer_user_id' => $this->referrer_user_id,
            'deletion_requested_at' => optional($this->deletion_requested_at)
                ->timestamp,
            'last_messaged_at' => optional($this->last_messaged_at)->timestamp,
            'last_authenticated_at' => optional($this->last_authenticated_at)
                ->timestamp,
            'updated_at' => optional($this->updated_at)->timestamp,
            'created_at' => optional($this->created_at)->timestamp,

            // Email subscription topics:
            'news_email_subscription_status' => isset(
                $this->email_subscription_topics,
            )
                ? in_array('news', $this->email_subscription_topics)
                : false,
            'lifestyle_email_subscription_status' => isset(
                $this->email_subscription_topics,
            )
                ? in_array('lifestyle', $this->email_subscription_topics)
                : false,
            'community_email_subscription_status' => isset(
                $this->email_subscription_topics,
            )
                ? in_array('community', $this->email_subscription_topics)
                : false,
            'scholarship_email_subscription_status' => isset(
                $this->email_subscription_topics,
            )
                ? in_array('scholarships', $this->email_subscription_topics)
                : false,
            'clubs_email_subscription_status' => isset(
                $this->email_subscription_topics,
            )
                ? in_array('clubs', $this->email_subscription_topics)
                : false,

            // SMS subscription topics:
            'general_sms_subscription_status' => isset(
                $this->sms_subscription_topics,
            )
                ? in_array('general', $this->sms_subscription_topics)
                : false,
            'voting_sms_subscription_status' => isset(
                $this->sms_subscription_topics,
            )
                ? in_array('voting', $this->sms_subscription_topics)
                : false,

            // Causes:
            'animal_welfare' => in_array('animal_welfare', $this->causes)
                ? true
                : false,
            'bullying' => in_array('bullying', $this->causes) ? true : false,
            'education' => in_array('education', $this->causes) ? true : false,
            'environment' => in_array('environment', $this->causes)
                ? true
                : false,
            'gender_rights_equality' => in_array(
                'gender_rights_equality',
                $this->causes,
            )
                ? true
                : false,
            'homelessness_poverty' => in_array(
                'homelessness_poverty',
                $this->causes,
            )
                ? true
                : false,
            'immigration_refugees' => in_array(
                'immigration_refugees',
                $this->causes,
            )
                ? true
                : false,
            'lgbtq_rights_equality' => in_array(
                'lgbtq_rights_equality',
                $this->causes,
            )
                ? true
                : false,
            'mental_health' => in_array('mental_health', $this->causes)
                ? true
                : false,
            'physical_health' => in_array('physical_health', $this->causes)
                ? true
                : false,
            'racial_justice_equity' => in_array(
                'racial_justice_equity',
                $this->causes,
            )
                ? true
                : false,
            'sexual_harassment_assault' => in_array(
                'sexual_harassment_assault',
                $this->causes,
            )
                ? true
                : false,

            // Voting method/plan:
            'voting_method' => strip_tags($this->voting_method),
            'voting_plan_status' => strip_tags($this->voting_plan_status),
            'voting_plan_method_of_transport' => strip_tags(
                $this->voting_plan_method_of_transport,
            ),
            'voting_plan_time_of_day' => strip_tags(
                $this->voting_plan_time_of_day,
            ),
            'voting_plan_attending_with' => strip_tags(
                $this->voting_plan_attending_with,
            ),
        ];

        // Only include email subscription status if we have that information.
        if (isset($this->email_subscription_status)) {
            $payload['email_subscription_status'] =
                $this->email_subscription_status;
            $payload['unsubscribed'] = !$this->email_subscription_status;
        }

        if (isset($this->feature_flags)) {
            if (array_key_exists('badges', $this->feature_flags)) {
                $payload['badges_feature_flag'] =
                    $this->feature_flags['badges'];
            }
            if (array_key_exists('refer-friends', $this->feature_flags)) {
                $payload['refer_friends_feature_flag'] =
                    $this->feature_flags['refer-friends'];
            }
            if (
                array_key_exists(
                    'refer-friends-scholarship',
                    $this->feature_flags,
                )
            ) {
                $payload['refer_friends_scholarship_feature_flag'] =
                    $this->feature_flags['refer-friends-scholarship'];
            }
        }

        // Fetch School information from GraphQL.
        if ($this->school_id) {
            $school = app(GraphQL::class)->getSchoolById($this->school_id);

            if (isset($school)) {
                $payload['school_name'] = $school['name'];
                $payload['school_state'] = $school['location']
                    ? substr($school['location'], 3)
                    : null;
            }
        }

        return $payload;
    }

    /**
     * Get payload for a club_id_updated event.
     *
     * @param  number $clubId
     * @return array
     */
    public function getClubIdUpdatedEventPayload($clubId)
    {
        $club = app(GraphQL::class)->getClubById($clubId);
        $clubLeader = app(self::class)->find(Arr::get($club, 'leaderId'));

        if (!$club || !$clubLeader) {
            return;
        }

        return [
            'club_name' => $club['name'],
            'club_leader_id' => $club['leaderId'],
            'club_leader_first_name' => $clubLeader->first_name,
            'club_leader_display_name' => $clubLeader->display_name,
            'club_leader_email' => $clubLeader->email,
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
        return $query
            ->where('campaigns', 'elemMatch', ['signup_id' => $id])
            ->orWhere('campaigns', 'elemMatch', ['signup_group' => $id])
            ->get();
    }

    /**
     * Update user with the given array of fields if field is not already set.
     * Filter out any fields that have a null value.
     *
     * @param  array $fields
     */
    public function updateIfNotSet($fields)
    {
        foreach (array_filter($fields) as $key => $value) {
            if (!isset($this->{$key})) {
                $this->{$key} = $value;
            }
        }
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
        if (!$token) {
            $tokenRepository = new DatabaseTokenRepository(
                app('db')->connection('mongodb'),
                app('hash'),
                config('auth.passwords.users.table'),
                config('app.key'),
                config('auth.passwords.users.expire'),
            );

            $token = $tokenRepository->create($this);
        }

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
        return $this->sendPasswordReset(
            PasswordResetType::get('FORGOT_PASSWORD'),
            $token,
        );
    }

    /**
     * Creates a call_to_action_email Customer.io event with a password reset URL.
     *
     * @param  string  $token
     * @param  string  $type
     * @return void
     */
    public function sendPasswordReset($type, $token = null)
    {
        $data = [
            'actionUrl' => $this->getPasswordResetUrl($token, $type),
            'type' => $this->type,
            'userId' => $this->id,
        ];

        /*
         * Use Customer.io events to track activate account emails, so admins can customize the
         * user's messaging journey per their source (e.g., Rock The Vote, newsletter subscription).
         */
        if (PasswordResetType::isActivateAccount($type)) {
            return CreateCustomerIoEvent::dispatch($this, 'call_to_action_email', $data);
        }

        // Send transactional emails for forgot password requests that don't need to be tracked.
        return SendCustomerIoEmail::dispatch(
            $this->email,
            config('services.customerio.app_api.transactional_message_ids.forgot_password'),
            $data
        );
    }

    /**
     * Add the given topic to the user's array of topics if it is not already there.
     *
     * @param string $topic
     */
    public function addEmailSubscriptionTopic($topic)
    {
        // Add the new topic to the existing array of topics
        $this->email_subscription_topics = array_merge(
            $this->email_subscription_topics ?: [],
            [$topic],
        );
    }

    /**
     * Mutator to ensure no duplicates in the email topics array.
     *
     * @param array $value
     */
    public function setEmailSubscriptionTopicsAttribute($value)
    {
        // Set de-duped array as email_subscription_topics
        $this->attributes['email_subscription_topics'] = array_values(
            array_unique($value),
        );
    }

    /**
     * Mutator to ensure null is not returned when user re-subscribes.
     *
     * @param array $value
     */
    public function getEmailSubscriptionTopicsAttribute($value)
    {
        //Ensure we always return an array value for the email_subscription_topics attribute.
        return empty($value) ? [] : $value;
    }

    /**
     * Mutator to ensure no duplicates in the SMS topics array.
     *
     * @param array $value
     */
    public function setSmsSubscriptionTopicsAttribute($value)
    {
        // Set de-duped array as sms_subscription_topics.
        $this->attributes['sms_subscription_topics'] = array_values(
            array_unique($value ?: []),
        );
    }

    /**
     * Sets default SMS subscription topics.
     */
    public function addDefaultSmsSubscriptionTopics()
    {
        $this->sms_subscription_topics = ['general', 'voting'];
    }

    /**
     * Sets default SMS subscription topics.
     */
    public function clearSmsSubscriptionTopics()
    {
        $this->sms_subscription_topics = [];
    }

    /**
     * Whether a SMS status value is a subscribed SMS status.
     *
     * @param string $smsStatusValue
     * @return bool
     */
    public static function isSubscribedSmsStatus($smsStatusValue)
    {
        return in_array($smsStatusValue, ['active', 'less']);
    }

    /**
     * Whether a SMS status value is a unsubscribed SMS status.
     *
     * @param string $smsStatusValue
     * @return bool
     */
    public static function isUnsubscribedSmsStatus($smsStatusValue)
    {
        return in_array($smsStatusValue, ['stop', 'undeliverable']);
    }

    /**
     * Whether user has a subscribed SMS status.
     *
     * @return bool
     */
    public function isSmsSubscribed()
    {
        return isset($this->sms_status) &&
            self::isSubscribedSmsStatus($this->sms_status);
    }

    /**
     * Whether user has any SMS subscription topics.
     *
     * @return bool
     */
    public function hasSmsSubscriptionTopics()
    {
        return isset($this->sms_subscription_topics) &&
            count($this->sms_subscription_topics);
    }

    /**
     * Accessor for the `causes` attribute.
     *
     * @param  mixed value
     * @return array
     */
    public function getCausesAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        // Fix formatting issue where some causes were saved as indexed objects
        // (e.g. {0: "animal_welfare", 1: "bullying"}) instead of an array.
        // Context: https://www.pivotaltracker.com/story/show/172005082
        return collect($value)
            ->values()
            ->all();
    }

    /**
     * Add the given cause to the user's array of causes if it is not already there.
     *
     * @param string $cause
     */
    public function addCause($cause)
    {
        // Add the new cause to the existing array of causes
        $this->causes = array_merge($this->causes ?: [], [$cause]);
    }

    /**
     * Mutator to ensure causes attribute is the correct data type.
     *
     * @param array $value
     */
    public function setCausesAttribute($value)
    {
        // Convert causes to an array and de-dupe
        $this->attributes['causes'] = array_values(array_unique($value));
    }

    /**
     * Accessor for the `school_id_preview` attribute.
     *
     * @return string
     */
    public function getSchoolIdPreviewAttribute()
    {
        $schoolId = $this->school_id;

        if (!isset($schoolId)) {
            return null;
        }

        if ($schoolId === 'school-not-available') {
            return $schoolId;
        }

        return substr($schoolId, 0, 3) . 'XXXXX';
    }

    /**
     * Mark this account for deletion.
     *
     * @return void
     */
    public function requestDeletion(): void
    {
        $this->deletion_requested_at = now();
        $this->save();
    }
}
