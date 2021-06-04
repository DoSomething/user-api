<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\RockTheVoteLog;
use App\Models\User;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(RockTheVoteLog::class, function (Faker $faker) {
    return [
        'contains_phone' => null,
        'finish_with_state' => 'No',
        'import_file_id' => $faker->randomDigitNotNull,
        'pre_registered' => 'No',
        'started_registration' => Carbon::now()->format('Y-m-d H:i:s O'),
        'status' => 'Step 1',
        'tracking_source' => 'ads',
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
    ];
});
