<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Club;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Club::class, function (Faker $faker) {
    return [
        'leader_id' => $this->faker->unique()->northstar_id,
        'name' => Str::title($faker->company),
        'city' => $faker->city,
        'location' => 'US-' . $faker->stateAbbr,
        'school_id' => $faker->school->school_id,
    ];
});
