<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\Feature;

use Asantibanez\LaravelEloquentStateMachines\Tests\TestCase;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestEnums\StatusEnum;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrderWithEnumCasting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class EnumCastingTransitionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function can_transition_to_any_state_using_enum_casting()
    {
        //Arrange
        $salesOrder = SalesOrderWithEnumCasting::create();

        $this->assertTrue($salesOrder->status()->is(StatusEnum::PENDING));

        $this->assertEquals(StatusEnum::PENDING, $salesOrder->status);

        //Act
        $salesOrder->status()->transitionTo(StatusEnum::APPROVED);

        //Assert
        $salesOrder->refresh();

        $this->assertTrue($salesOrder->status()->is(StatusEnum::APPROVED));

        $this->assertEquals(StatusEnum::APPROVED, $salesOrder->status);
    }

    /** @test */
    public function can_postpone_transition_to_any_state_using_enum_casting()
    {
        //Arrange
        $salesOrder = SalesOrderWithEnumCasting::create();

        $this->assertTrue($salesOrder->status()->is(StatusEnum::PENDING));

        $this->assertEquals(StatusEnum::PENDING, $salesOrder->status);

        //Act
        $pendingTransition = $salesOrder->status()->postponeTransitionTo(StatusEnum::APPROVED, Carbon::tomorrow()->startOfDay());

        //Assert
        $this->assertNotNull($pendingTransition);

        $salesOrder->refresh();

        $this->assertTrue($salesOrder->status()->is('pending'));

        $this->assertTrue($salesOrder->status()->hasPendingTransitions());

        /** @var PendingTransition $pendingTransition */
        $pendingTransition = $salesOrder->status()->pendingTransitions()->first();

        $this->assertEquals('status', $pendingTransition->field);
        $this->assertEquals('pending', $pendingTransition->from);
        $this->assertEquals('approved', $pendingTransition->to);

        $this->assertEquals(Carbon::tomorrow()->startOfDay(), $pendingTransition->transition_at);

        $this->assertNull($pendingTransition->applied_at);

        $this->assertEquals($salesOrder->id, $pendingTransition->model->id);
    }

    /** @test */
    public function can_access_model_state_history_using_enum_casting()
    {
        //Arrange
        $salesOrder = SalesOrderWithEnumCasting::create();

        $this->assertTrue($salesOrder->status()->is(StatusEnum::PENDING));

        $this->assertEquals(StatusEnum::PENDING, $salesOrder->status);

        //Act
        $salesOrder->status()->transitionTo(StatusEnum::APPROVED);

        //Assert
        $salesOrder->refresh();

        $this->assertTrue($salesOrder->status()->is(StatusEnum::APPROVED));

        $this->assertEquals(StatusEnum::APPROVED, $salesOrder->status);

        $this->assertTrue($salesOrder->status()->was(StatusEnum::PENDING));
    }
}
