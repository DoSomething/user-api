<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\GroupType;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(GroupType::class, function (Faker $faker) {
    $job = Str::title($faker->unique()->jobTitle);
    $number = $faker->unique()->numberBetween(100, 9999);

    return [
        'name' => "National $job Society Chapter #$number",
        'filter_by_location' => true,
    ];
});
