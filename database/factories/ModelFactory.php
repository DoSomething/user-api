<?php

use App\Models\Action;
use App\Models\ActionStat;
use App\Models\Campaign;
use App\Models\Club;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Signup;
use App\Models\User;
use App\Types\ActionType;
use App\Types\Cause;
use App\Types\PostType;
use App\Types\TimeCommitment;
use Faker\Generator;
use Illuminate\Support\Str;

/**
 * Define all of our model factories. Model factories give
 * you a convenient way to create models for testing and seeding your
 * database. Just tell the factory how a default model should look.
 *
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    $faker->addProvider(new FakerPhoneNumber($faker));

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->optional(0.5)->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobile' => $faker->unique()->phoneNumber,
        'mobilecommons_id' => $faker->randomNumber(5),
        'sms_status' => $faker->randomElement(['active', 'less']),
        'email_subscription_status' => $faker->boolean,
        'facebook_id' => $faker->unique()->randomNumber(),
        'google_id' => $faker->unique()->randomNumber(),
        'password' => $faker->password,
        'birthdate' => $faker->dateTimeBetween('1/1/1980', '-1 year'),
        'addr_street1' => $faker->streetAddress,
        'addr_city' => $faker->city,
        'addr_state' => $faker->stateAbbr,
        'addr_zip' => $faker->postcode,
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'source' => 'factory',
        'club_id' => 1,
        'causes' => $faker->randomElements(
            [
                'animal_welfare',
                'bullying',
                'education',
                'environment',
                'gender_rights_equality',
                'homelessness_poverty',
                'immigration_refugees',
                'lgbtq_rights_equality',
                'mental_health',
                'physical_health',
                'racial_justice_equity',
                'sexual_harassment_assault',
            ],
            $faker->numberBetween(0, 6),
        ),
        /*
         * Set email subscription status to null by default, as it won't be set for all users.
         * e.g. when source is 'sms'
         */
        'email_subscription_status' => null,
    ];
});

$factory->state(App\Models\User::class, 'email-subscribed', function (
    Faker\Generator $faker
) {
    return [
        'email_subscription_status' => true,
        'email_subscription_topics' => $faker->randomElements(
            ['news', 'lifestyle', 'actions', 'scholarships'],
            $faker->numberBetween(1, 4),
        ),
    ];
});

$factory->state(App\Models\User::class, 'email-unsubscribed', function (
    Faker\Generator $faker
) {
    return [
        'email_subscription_status' => false,
    ];
});

$factory->state(App\Models\User::class, 'sms-subscribed', function (
    Faker\Generator $faker
) {
    return [
        'sms_status' => 'active',
        // Note: Not all users will have SMS subscription topics, it was added in March 2020.
        'sms_subscription_topics' => ['voting'],
    ];
});

$factory->state(App\Models\User::class, 'sms-unsubscribed', function (
    Faker\Generator $faker
) {
    return [
        'sms_status' => 'stop',
        'sms_subscription_topics' => null,
    ];
});

$factory->defineAs(App\Models\User::class, 'staff', function (
    Faker\Generator $faker
) {
    $faker->addProvider(new FakerPhoneNumber($faker));

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobile' => $faker->unique()->phoneNumber,
        'facebook_id' => $faker->unique()->randomNumber(),
        'google_id' => $faker->unique()->randomNumber(),
        'password' => $faker->password,
        'birthdate' => $faker->dateTimeBetween('1/1/1980', '-1 year'),
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'source' => 'factory',
        'role' => 'staff',
    ];
});

$factory->defineAs(App\Models\User::class, 'admin', function (
    Faker\Generator $faker
) {
    $faker->addProvider(new FakerPhoneNumber($faker));

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobile' => $faker->unique()->phoneNumber,
        'facebook_id' => $faker->unique()->randomNumber(),
        'google_id' => $faker->unique()->randomNumber(),
        'password' => $faker->password,
        'birthdate' => $faker->dateTimeBetween('1/1/1980', '-1 year'),
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'source' => 'factory',
        'role' => 'admin',
    ];
});

$factory->defineAs(\App\Models\Client::class, 'authorization_code', function (
    Faker\Generator $faker
) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'authorization_code',
        'scope' => ['user', 'openid', 'profile', 'role:staff', 'role:admin'],
        'redirect_uri' => $faker->url,
    ];
});

$factory->defineAs(\App\Models\Client::class, 'password', function (
    Faker\Generator $faker
) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'password',
        'scope' => ['user', 'profile', 'role:staff', 'role:admin'],
    ];
});

$factory->defineAs(\App\Models\Client::class, 'client_credentials', function (
    Faker\Generator $faker
) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'client_credentials',
        'scope' => ['user', 'admin'],
    ];
});

// Action Factory
$factory->define(Action::class, function (Generator $faker) {
    return [
        'name' => Str::title($this->faker->unique()->words(3, true)),
        'campaign_id' => factory(Campaign::class)->create()->id,
        'post_type' => 'photo',
        'action_type' => $this->faker->randomElement(ActionType::all()),
        'time_commitment' => $this->faker->randomElement(TimeCommitment::all()),
        'reportback' => true,
        'civic_action' => true,
        'scholarship_entry' => true,
        'anonymous' => false,
        'noun' => 'things',
        'verb' => 'done',
        'collect_school_id' => true,
        'volunteer_credit' => false,
    ];
});

$factory->state(Action::class, 'voter-registration', [
    'action_type' => ActionType::ATTEND_EVENT(),
    'name' => 'VR-' . $this->faker->unique()->year . ' Voter Registrations',
    'noun' => 'registrations',
    'post_type' => PostType::VOTER_REG(),
    'verb' => 'completed',
]);

// Post Factory
$factory->define(Post::class, function (Generator $faker) {
    $faker->addProvider(new FakerNorthstarId($faker));
    $faker->addProvider(new FakerPostUrl($faker));
    $faker->addProvider(new FakerSchoolId($faker));

    return [
        'campaign_id' => function () {
            return factory(Campaign::class)->create()->id;
        },
        'signup_id' => function (array $attributes) {
            // If a 'signup_id' is not provided, create one for the same Campaign & Northstar ID.
            return factory(Signup::class)->create([
                'campaign_id' => $attributes['campaign_id'],
                'northstar_id' => $attributes['northstar_id'],
            ])->id;
        },
        'action_id' => function (array $attributes) {
            return factory(Action::class)->create([
                'campaign_id' => $attributes['campaign_id'],
            ])->id;
        },
        'northstar_id' => $this->faker->northstar_id,
        'text' => $faker->sentence(),
        'location' => 'US-' . $faker->stateAbbr(),
        'source' => 'phpunit',
    ];
});

/*
 * Post type factory states.
 */
$factory->state(Post::class, 'photo', function (Generator $faker) {
    return [
        'type' => 'photo',
        'quantity' => $faker->randomNumber(2),
        'url' => $faker->post_url,
    ];
});

$factory->state(Post::class, 'text', [
    'type' => 'text',
]);

$factory->state(Post::class, 'voter-reg', [
    'type' => 'voter-reg',
]);

/*
 * Post status factory states.
 */
$factory->state(Post::class, 'accepted', [
    'status' => 'accepted',
]);

$factory->state(Post::class, 'pending', [
    'status' => 'pending',
]);

$factory->state(Post::class, 'rejected', [
    'status' => 'rejected',
]);

$factory->state(Post::class, 'step-1', [
    'status' => 'step-1',
]);

$factory->state(Post::class, 'register-form', [
    'status' => 'register-form',
]);

$factory->state(Post::class, 'register-OVR', [
    'status' => 'register-OVR',
]);

// Signup Factory
$factory->define(Signup::class, function (Generator $faker) {
    $faker->addProvider(new FakerNorthstarId($faker));

    return [
        'northstar_id' => $faker->northstar_id,
        'campaign_id' => function () {
            return factory(Campaign::class)->create()->id;
        },
        'why_participated' => $faker->sentence(),
        'source' => 'phpunit',
        'details' => $faker->randomElement([
            null,
            'fun-affiliate-stuff',
            'i-say-the-tails',
        ]),
    ];
});

// Reaction Factory
$factory->define(Reaction::class, function (Generator $faker) {
    $faker->addProvider(new FakerNorthstarId($faker));

    return [
        'northstar_id' => $faker->northstar_id,
        'post_id' => $faker->randomNumber(7),
    ];
});

// Base User Factory
$factory->define(User::class, function (Generator $faker) {
    $faker->addProvider(new FakerNorthstarId($faker));

    return [
        'northstar_id' => $faker->northstar_id,
        'access_token' => Str::random(1024),
        'access_token_expiration' => Str::random(11),
        'refresh_token' => Str::random(1024),
        'remember_token' => Str::random(10),
        'role' => 'user',
    ];
});

$factory->defineAs(User::class, 'admin', function () use ($factory) {
    return array_merge($factory->raw(User::class), ['role' => 'admin']);
});

$factory->defineAs(User::class, 'staff', function () use ($factory) {
    return array_merge($factory->raw(User::class), ['role' => 'staff']);
});

// Campaign Factory
$factory->define(Campaign::class, function (Generator $faker) {
    return [
        'internal_title' => Str::title($faker->unique()->catchPhrase),
        'cause' => $faker->randomElements(Cause::all(), rand(1, 5)),
        'impact_doc' => 'https://www.google.com/',
        // By default, we create an "open" campaign.
        'start_date' => $faker
            ->dateTimeBetween('-6 months', 'now')
            ->setTime(0, 0),
        'end_date' => $faker
            ->dateTimeBetween('+1 months', '+6 months')
            ->setTime(0, 0),
    ];
});

$factory->defineAs(Campaign::class, 'closed', function (Generator $faker) use (
    $factory
) {
    return array_merge($factory->raw(Campaign::class), [
        'start_date' => $faker
            ->dateTimeBetween('-12 months', '-6 months')
            ->setTime(0, 0),
        'end_date' => $faker
            ->dateTimeBetween('-3 months', 'yesterday')
            ->setTime(0, 0),
    ]);
});

$factory->state(Campaign::class, 'voter-registration', function (
    Generator $faker
) {
    return [
        'cause' => [Cause::VOTER_REGISTRATION()],
        'internal_title' =>
            'Voter Registration ' . Str::title($faker->unique()->catchPhrase),
    ];
});

// Group Type Factory
$factory->define(GroupType::class, function (Generator $faker) {
    return [
        'name' =>
            'National ' . Str::title($faker->unique()->jobTitle) . ' Society',
        'filter_by_location' => true,
    ];
});

// Group Factory
$factory->define(Group::class, function (Generator $faker) {
    $faker->addProvider(new FakerSchoolId($faker));

    return [
        'group_type_id' => function () {
            return factory(GroupType::class)->create()->id;
        },
        'name' => Str::title($faker->unique()->company),
        'city' => $faker->city,
        'location' => 'US-' . $faker->stateAbbr,
    ];
});

$factory->state(Group::class, 'school', function (Generator $faker) {
    $school = $faker->school;

    return [
        'city' => $school->city,
        'location' => $school->location,
        'school_id' => $school->school_id,
    ];
});

// Club Factory
$factory->define(Club::class, function (Generator $faker) {
    return [
        'leader_id' => $this->faker->unique()->northstar_id,
        'name' => Str::title($faker->company),
        'city' => $faker->city,
        'location' => 'US-' . $faker->stateAbbr,
        'school_id' => $faker->school->school_id,
    ];
});

// ActionStat Factory
$factory->define(ActionStat::class, function (Generator $faker) {
    $school = $faker->unique()->school;

    return [
        'school_id' => $school->school_id,
        'location' => $school->location,
        'action_id' => function () {
            return factory(Action::class)->create()->id;
        },
        'impact' => $faker->randomNumber(2),
    ];
});
