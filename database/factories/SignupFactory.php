<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Signup;
use Faker\Generator as Faker;

$factory->define(Signup::class, function (Faker $faker) {
    $faker->addProvider(new FakerNorthstarId($faker));

    return [
        'northstar_id' => $faker->northstar_id,
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
