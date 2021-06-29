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

$factory->state(User::class, 'email-subscribed', function (Faker $faker) {
    return [
        'email_subscription_status' => true,
        'email_subscription_topics' => $faker->randomElements(
            //removing news from this state because it causes errors related to badge creation
            //and more updates being sent to customer.io than shown in tests related to subscription
            ['lifestyle', 'community', 'scholarships'],
            $faker->numberBetween(1, 3),
        ),
    ];
});

$factory->state(User::class, 'email-subscribed-community', function () {
    return [
        'email_subscription_status' => true,
        'email_subscription_topics' => ['community'],
    ];
});

$factory->state(User::class, 'email-subscribed-news', function () {
    return [
        'email_subscription_status' => true,
        'email_subscription_topics' => ['news'],
    ];
});

$factory->state(User::class, 'email-unsubscribed', function () {
    return [
        'email_subscription_status' => false,
    ];
});

$factory->state(User::class, 'sms-subscribed', function () {
    return [
        'sms_status' => 'active',
        // Note: Not all users will have SMS subscription topics, it was added in March 2020.
        'sms_subscription_topics' => ['voting'],
    ];
});

$factory->state(User::class, 'sms-unsubscribed', function () {
    return [
        'sms_status' => 'stop',
        'sms_subscription_topics' => null,
    ];
});

$factory->state(User::class, 'staff', function (Faker $faker) {
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

$factory->state(User::class, 'admin', function (Faker $faker) {
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
