<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\GroupType;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(GroupType::class, function (Faker $faker) {
    return [
        'name' =>
            'National ' . Str::title($faker->unique()->jobTitle) . ' Society',
        'filter_by_location' => true,
    ];
});
