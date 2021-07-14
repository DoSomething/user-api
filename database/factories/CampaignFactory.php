<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Campaign;
use App\Types\Cause;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Campaign::class, function (Faker $faker) {
    return [
        'internal_title' => Str::title($faker->unique()->catchPhrase),
        'cause' => $faker->randomElements(Cause::all(), rand(1, 5)),
        'impact_doc' => 'https://www.google.com/',
        // By default, we create an "open" campaign.
        'start_date' => $faker
            ->dateTimeBetween('-6 months', 'now')
            ->setTime(0, 0),
        'end_date' => $faker
            ->dateTimeBetween('+1 months', '+6 months')
            ->setTime(0, 0),
    ];
});

/*
 * Campaign factory states.
 */

$factory->state(Campaign::class, 'closed', function (Faker $faker) use (
    $factory
) {
    return array_merge($factory->raw(Campaign::class), [
        'start_date' => $faker
            ->dateTimeBetween('-12 months', '-6 months')
            ->setTime(0, 0),
        'end_date' => $faker
            ->dateTimeBetween('-3 months', 'yesterday')
            ->setTime(0, 0),
    ]);
});

$factory->state(Campaign::class, 'voter-registration', function (Faker $faker) {
    return [
        'cause' => [Cause::VOTER_REGISTRATION()],
        'internal_title' =>
            'Voter Registration ' . Str::title($faker->unique()->catchPhrase),
    ];
});
