<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    $faker->addProvider(new FakerPhoneNumber($faker));

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->optional(0.5)->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobilecommons_id' => $faker->randomNumber(5),
        //'sms_status' => $faker->randomElement(['active', 'less']),
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

$factory->state(User::class, 'email-subscribed', function (Faker $faker) {
    return [
        'email_subscription_status' => true,
        'email_subscription_topics' => $faker->randomElements(
            ['news', 'lifestyle', 'actions', 'scholarships'],
            $faker->numberBetween(1, 4),
        ),
    ];
});

$factory->state(User::class, 'email-unsubscribed', function () {
    return [
        'email_subscription_status' => false,
    ];
});

$factory->state(User::class, 'sms-subscribed', function (Faker $faker) {
    return [
        'mobile' => $faker->unique()->phoneNumber,
        'sms_status' => 'active',
    ];
});

$factory->state(User::class, 'sms-less', function (Faker $faker) {
    return [
        'mobile' => $faker->unique()->phoneNumber,
        'sms_status' => 'less',
    ];
});

$factory->state(User::class, 'sms-unsubscribed', function (Faker $faker) {
    return [
        'mobile' => $faker->unique()->phoneNumber,
        'sms_status' => 'stop',
    ];
});

$factory->defineAs(User::class, 'staff', function (Faker $faker) {
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

$factory->defineAs(User::class, 'admin', function (Faker $faker) {
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
