<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ActionStat;
use Faker\Generator as Faker;

$factory->define(ActionStat::class, function (Faker $faker) {
    $school = $faker->unique()->school;

    return [
        'school_id' => $school->school_id,
        'location' => $school->location,
        'action_id' => function () {
            return factory(Action::class)->create()->id;
        },
        'impact' => $faker->randomNumber(2),
    ];
});
