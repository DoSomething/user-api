<?php

/**
 * Define all of our model factories. Model factories give
 * you a convenient way to create models for testing and seeding your
 * database. Just tell the factory how a default model should look.
 *
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Northstar\Models\User::class, function (Faker\Generator $faker) {
    $faker->addProvider(new FakerPhoneNumber($faker));

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->optional(0.5)->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobile' => $faker->unique()->phoneNumber,
        'mobilecommons_id' => $faker->randomNumber(5),
        'sms_status' => $faker->randomElement(['active', 'undeliverable']),
        'facebook_id' => $faker->unique()->randomNumber(),
        'google_id' => $faker->unique()->randomNumber(),
        'password' => $faker->password,
        'birthdate' => $faker->dateTimeBetween('1/1/1980', '-1 year'),
        'addr_street1' => $faker->streetAddress,
        'city' => $faker->city,
        'addr_state' => $faker->stateAbbr,
        'addr_zip' => $faker->postcode,
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'source' => 'factory',
        'school_id' => '12500012',
        'causes' => $faker->randomElements(['animal_welfare', 'bullying', 'education', 'environment', 'gender_rights_equality', 'homelessness_poverty', 'immigration_refugees', 'lgbtq_rights_equality', 'mental_health', 'physical_health', 'racial_justice_equity', 'sexual_harassment_assault'], $faker->numberBetween(0, 6)),
        /**
         * Set email subscription status to null by default, as it won't be set for all users.
         * e.g. when source is 'sms'
         */
        'email_subscription_status' => null,
    ];
});

$factory->state(Northstar\Models\User::class, 'email-subscribed', function (Faker\Generator $faker) {
    return [
        'email_subscription_status' => true,
        'email_subscription_topics' => $faker->randomElements(['news', 'lifestyle', 'actions', 'scholarships'], $faker->numberBetween(1, 4)),
    ];
});

$factory->state(Northstar\Models\User::class, 'email-unsubscribed', function (Faker\Generator $faker) {
    return [
        'email_subscription_status' => false,
    ];
});

$factory->defineAs(Northstar\Models\User::class, 'staff', function (Faker\Generator $faker) {
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

$factory->defineAs(Northstar\Models\User::class, 'admin', function (Faker\Generator $faker) {
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

$factory->defineAs(\Northstar\Models\Client::class, 'authorization_code', function (Faker\Generator $faker) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'authorization_code',
        'scope' => ['user', 'openid', 'profile', 'role:staff', 'role:admin'],
        'redirect_uri' => $faker->url,
    ];
});

$factory->defineAs(\Northstar\Models\Client::class, 'password', function (Faker\Generator $faker) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'password',
        'scope' => ['user', 'profile', 'role:staff', 'role:admin'],
    ];
});

$factory->defineAs(\Northstar\Models\Client::class, 'client_credentials', function (Faker\Generator $faker) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'client_credentials',
        'scope' => ['user', 'admin'],
    ];
});
