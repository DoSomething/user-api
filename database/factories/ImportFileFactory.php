<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ImportFile;
use App\Types\ImportType;
use Faker\Generator as Faker;

$factory->define(ImportFile::class, function (Faker $faker) {
    return [
        'id' => $faker->randomDigitNotNull,
        'filepath' => $faker->imageUrl,
        'import_type' => ImportType::$rockTheVote,
        'row_count' => $faker->numberBetween(10, 1250),
    ];
});
