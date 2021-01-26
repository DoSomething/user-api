<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Post;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    $faker->addProvider(new FakerNorthstarId($faker));
    $faker->addProvider(new FakerPostUrl($faker));
    $faker->addProvider(new FakerSchoolId($faker));

    return [
        'campaign_id' => function () {
            return factory(Campaign::class)->create()->id;
        },
        'signup_id' => function (array $attributes) {
            // If a 'signup_id' is not provided, create one for the same Campaign & Northstar ID.
            return factory(Signup::class)->create([
                'campaign_id' => $attributes['campaign_id'],
                'northstar_id' => $attributes['northstar_id'],
            ])->id;
        },
        'action_id' => function (array $attributes) {
            return factory(Action::class)->create([
                'campaign_id' => $attributes['campaign_id'],
            ])->id;
        },
        'northstar_id' => $this->faker->northstar_id,
        'text' => $faker->sentence(),
        'location' => 'US-' . $faker->stateAbbr(),
        'source' => 'phpunit',
    ];
});

/*
 * Post type factory states.
 */
$factory->state(Post::class, 'photo', function (Faker $faker) {
    return [
        'type' => 'photo',
        'quantity' => $faker->randomNumber(2),
        'url' => $faker->post_url,
    ];
});

$factory->state(Post::class, 'text', [
    'type' => 'text',
]);

$factory->state(Post::class, 'voter-reg', [
    'type' => 'voter-reg',
]);

/*
 * Post status factory states.
 */
$factory->state(Post::class, 'accepted', [
    'status' => 'accepted',
]);

$factory->state(Post::class, 'pending', [
    'status' => 'pending',
]);

$factory->state(Post::class, 'rejected', [
    'status' => 'rejected',
]);

$factory->state(Post::class, 'step-1', [
    'status' => 'step-1',
]);

$factory->state(Post::class, 'register-form', [
    'status' => 'register-form',
]);

$factory->state(Post::class, 'register-OVR', [
    'status' => 'register-OVR',
]);
