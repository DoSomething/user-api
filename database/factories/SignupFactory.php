<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Campaign;
use App\Models\Signup;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Signup::class, function (Faker $faker) {
    $faker->addProvider(new FakerNorthstarId($faker));

    return [
        'northstar_id' => function () {
            return factory(User::class)->create()->id;
        },
        'campaign_id' => function () {
            return factory(Campaign::class)->create()->id;
        },
        'why_participated' => $faker->sentence(),
        'source' => 'phpunit',
        'details' => $faker->randomElement([
            null,
            'fun-affiliate-stuff',
            'i-say-the-tails',
        ]),
    ];
});
