<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\RockTheVoteReport;
use Faker\Generator as Faker;

$factory->define(RockTheVoteReport::class, function (Faker $faker) {
    return [
        'id' => $faker->randomDigitNotNull,
        'status' => 'queued',
    ];
});
