<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Reaction;
use Faker\Generator as Faker;

$factory->define(Reaction::class, function (Faker $faker) {
    $faker->addProvider(new FakerNorthstarId($faker));

    return [
        'northstar_id' => $faker->northstar_id,
        'post_id' => $faker->randomNumber(7),
    ];
});
