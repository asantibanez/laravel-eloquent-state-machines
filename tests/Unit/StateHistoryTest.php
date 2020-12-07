<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\Unit;

use Asantibanez\LaravelEloquentStateMachines\Models\StateHistory;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class StateHistoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function can_get_custom_property()
    {
        //Arrange
        $comments = $this->faker->sentence;

        $stateHistory = factory(StateHistory::class)->create([
            'custom_properties' => [
                'comments' => $comments,
            ]
        ]);

        //Act
        $result = $stateHistory->getCustomProperty('comments');

        //Assert
        $this->assertEquals($comments, $result);
    }

    /** @test */
    public function can_get_all_custom_properties()
    {
        //Arrange
        $customProperties = [
            'amount' => $this->faker->numberBetween(1, 100),
            'comments' => $this->faker->sentence,
            'approved_by' => $this->faker->randomDigitNotNull,
        ];

        $stateHistory = factory(StateHistory::class)->create([
            'custom_properties' => $customProperties
        ]);

        //Act
        $result = $stateHistory->allCustomProperties();

        //Assert
        $this->assertEquals($customProperties, $result);
    }
}
