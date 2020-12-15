<?php

use Asantibanez\LaravelEloquentStateMachines\Models\PendingTransition;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(PendingTransition::class, function (Faker $faker) {
    return [
        'field' => $faker->word,
        'from' => $faker->word,
        'to' => $faker->word,

        'transition_at' => Carbon::tomorrow(),

        'model_id' => $faker->randomDigitNotNull,
        'model_type' => $faker->word,
    ];
});
