<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Client;
use Faker\Generator as Faker;

$factory->defineAs(Client::class, 'authorization_code', function (
    Faker $faker
) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'authorization_code',
        'scope' => ['user', 'openid', 'profile', 'role:staff', 'role:admin'],
        'redirect_uri' => $faker->url,
    ];
});

$factory->defineAs(Client::class, 'password', function (Faker $faker) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'password',
        'scope' => ['user', 'profile', 'role:staff', 'role:admin'],
    ];
});

$factory->defineAs(Client::class, 'client_credentials', function (
    Faker $faker
) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'client_credentials',
        'scope' => ['user', 'admin'],
    ];
});
