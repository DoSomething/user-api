<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Action;
use App\Models\Campaign;
use App\Types\ActionType;
use App\Types\PostType;
use App\Types\TimeCommitment;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Action::class, function (Faker $faker) {
    return [
        'name' => Str::title($faker->unique()->words(3, true)),
        'campaign_id' => factory(Campaign::class)->create()->id,
        'post_type' => 'photo',
        'action_type' => $faker->randomElement(ActionType::all()),
        'time_commitment' => $faker->randomElement(TimeCommitment::all()),
        'reportback' => true,
        'civic_action' => true,
        'scholarship_entry' => true,
        'anonymous' => false,
        'noun' => 'things',
        'verb' => 'done',
        'collect_school_id' => true,
        'volunteer_credit' => false,
    ];
});

/*
 * Action factory states.
 */
$factory->state(Action::class, 'voter-registration', function (Faker $faker) {
    return [
        'action_type' => ActionType::ATTEND_EVENT(),
        'name' => 'VR-' . $faker->unique()->year . ' Voter Registrations',
        'noun' => 'registrations',
        'post_type' => PostType::VOTER_REG(),
        'verb' => 'completed',
    ];
});

$factory->state(Action::class, 'callpower', function (Faker $faker) {
    return [
        'post_type' => PostType::PHONE_CALL(),
        'action_type' => ActionType::HAVE_CONVERSATION(),
        'callpower_campaign_id' => $faker->unique()->numberBetween(1, 9000),
        'name' => 'Call Your Representative ' . $faker->unique()->year,
        'noun' => 'calls',
        'verb' => 'completed',
    ];
});

$factory->state(Action::class, 'softedge', function (Faker $faker) {
    return [
        'post_type' => PostType::EMAIL(),
        'action_type' => ActionType::CONTACT_DECISIONMAKER(),
        'name' => 'Email Your Representative ' . $faker->unique()->year,
        'noun' => 'email',
        'verb' => 'sent',
    ];
});
