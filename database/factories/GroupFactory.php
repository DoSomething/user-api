<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Group;
use App\Models\GroupType;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Group::class, function (Faker $faker) {
    $faker->addProvider(new FakerSchoolId($faker));

    return [
        'group_type_id' => function () {
            return factory(GroupType::class)->create()->id;
        },
        'name' => Str::title($faker->unique()->company),
        'city' => $faker->city,
        'location' => 'US-' . $faker->stateAbbr,
    ];
});

/*
 * Group factory states.
 */
$factory->state(Group::class, 'school', function (Faker $faker) {
    $school = $faker->school;

    return [
        'city' => $school->city,
        'location' => $school->location,
        'school_id' => $school->school_id,
    ];
});
