<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\RockTheVoteReport;
use Faker\Generator as Faker;

$factory->define(RockTheVoteReport::class, function (Faker $faker) {
    return [
        'id' => $faker->randomDigitNotNull,
        'status' => 'queued',
        'current_index' => 0,
        'since' => '2021-05-26 10:07:00',
        'before' => '2021-05-26 11:37:00',
    ];
});
