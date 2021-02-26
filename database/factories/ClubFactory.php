<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Club;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Club::class, function (Faker $faker) {
    $faker->addProvider(new FakerSchoolId($faker));

    return [
        'leader_id' => function () {
            return factory(User::class)->create()->id;
        },
        'name' => Str::title($faker->company),
        'city' => $faker->city,
        'location' => 'US-' . $faker->stateAbbr,
        'school_id' => $faker->school->school_id,
    ];
});
