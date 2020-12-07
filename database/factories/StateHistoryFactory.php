<?php

use Asantibanez\LaravelEloquentStateMachines\Models\StateHistory;
use Faker\Generator as Faker;

$factory->define(StateHistory::class, function (Faker $faker) {
    return [
        'field' => $faker->word,
        'from' => $faker->word,
        'to' => $faker->word,

        'model_id' => $faker->randomDigitNotNull,
        'model_type' => $faker->word,
    ];
});
