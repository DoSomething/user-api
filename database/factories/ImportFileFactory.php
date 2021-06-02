<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ImportFile;
use App\Types\ImportType;
use Faker\Generator as Faker;

$factory->define(ImportFile::class, function (Faker $faker) {
    $rowCount = $faker->numberBetween(10, 1250);
    $importCount = $faker->numberBetween(1, $rowCount);

    return [
        'user_id' => null,
        'import_type' => $faker->randomElement([
            ImportType::$emailSubscription,
            ImportType::$mutePromotions,
            ImportType::$rockTheVote,
        ]),
        'options' => null,
        'filepath' => $faker->imageUrl,
        'row_count' => $rowCount,
        'import_count' => $importCount,
        'skip_count' => $rowCount - $importCount,
    ];
});

$factory->state(ImportFile::class, 'email_subscription', [
    'import_type' => ImportType::$emailSubscription,
]);

$factory->state(ImportFile::class, 'mute_promotions', [
    'import_type' => ImportType::$mutePromotions,
]);

$factory->state(ImportFile::class, 'rock_the_vote', [
    'import_type' => ImportType::$rockTheVote,
]);
