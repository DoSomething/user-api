<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ImportFile;
use App\Types\ImportType;
use Faker\Generator as Faker;

$factory->define(ImportFile::class, function (Faker $faker) {
    return [
        'filepath' => $faker->imageUrl,
        'import_type' => $faker->randomElement([
            ImportType::$emailSubscription,
            ImportType::$mutePromotions,
            ImportType::$rockTheVote,
        ]),
        'row_count' => $faker->numberBetween(10, 1250),
    ];
});

$factory->state(ImportFile::class, 'email_subscription', [
    'import_type' => ImportType::$emailSubscription,
]);

$factory->state(ImportFile::class, 'mute_promotions', [
    'import_type' => ImportType::$emailSubscription,
]);

$factory->state(ImportFile::class, 'rock_the_vote', [
    'import_type' => ImportType::$emailSubscription,
]);
