<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\Feature;

use Asantibanez\LaravelEloquentStateMachines\Exceptions\TransitionNotAllowedException;
use Asantibanez\LaravelEloquentStateMachines\Models\PendingTransition;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestJobs\StartSalesOrderFulfillmentJob;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestCase;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrder;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders\FulfillmentStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders\StatusStateMachine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Queue;

class HasStateMachinesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function can_configure_state_machines()
    {
        //Act
        $salesOrder = factory(SalesOrder::class)->create();

        $this->assertEquals(StatusStateMachine::class, $salesOrder->stateMachines['status']);
        $this->assertEquals(FulfillmentStateMachine::class, $salesOrder->stateMachines['fulfillment']);

        //Assert
        $this->assertNotNull($salesOrder->status());

        $this->assertNotNull($salesOrder->fulfillment());
    }

    /** @test */
    public function should_set_default_state_for_field()
    {
        //Arrange
        $statusStateMachine = new StatusStateMachine('status', $salesOrder);

        $fulfillmentStateMachine = new FulfillmentStateMachine('fulfillment', $salesOrder);

        //Act
        $salesOrder = factory(SalesOrder::class)->create();

        //Assert
        $this->assertEquals($statusStateMachine->defaultState(), $salesOrder->status);
        $this->assertEquals($statusStateMachine->defaultState(), $salesOrder->status()->state);
        $this->assertEquals(1, $salesOrder->status()->history()->count());

        $this->assertEquals($fulfillmentStateMachine->defaultState(), $salesOrder->fulfillment);
        $this->assertEquals($fulfillmentStateMachine->defaultState(), $salesOrder->fulfillment()->state);
        $this->assertEquals(0, $salesOrder->fulfillment()->history()->count());
    }

    /** @test */
    public function should_transition_to_next_state()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();

        $this->assertTrue($salesOrder->status()->is('pending'));

        $this->assertEquals('pending', $salesOrder->status);

        //Act
        $salesOrder->status()->transitionTo('approved');

        //Assert
        $salesOrder->refresh();

        $this->assertTrue($salesOrder->status()->is('approved'));

        $this->assertEquals('approved', $salesOrder->status);
    }

    /** @test */
    public function can_check_next_possible_transitions()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();

        $this->assertTrue($salesOrder->status()->is('pending'));

        //Act - Assert
        $this->assertTrue($salesOrder->status()->canBe('approved'));

        $this->assertFalse($salesOrder->status()->canBe('declined'));
    }

    /** @test */
    public function should_throw_exception_for_invalid_state_on_transition()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create([
            'status' => 'approved',
        ]);

        $this->assertFalse($salesOrder->status()->canBe('pending'));

        //Act
        try {
            $salesOrder->status()->transitionTo('pending');
            $this->fail('Should have thrown exception');
        } catch (\Throwable $throwable) {
            //Assert
            $this->assertTrue($throwable instanceof TransitionNotAllowedException);
        }
    }

    /** @test */
    public function should_throw_exception_for_custom_validator_on_transition()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();

        $this->assertTrue($salesOrder->status()->is('pending'));

        $this->assertTrue($salesOrder->fulfillment()->is(null));

        $this->assertTrue($salesOrder->fulfillment()->canBe('pending'));

        //Act
        try {
            $salesOrder->fulfillment()->transitionTo('pending');
            $this->fail('Should have thrown exception');
        } catch (\Throwable $throwable) {
            // Assert
            $this->assertTrue($throwable instanceof ValidationException);
        }
    }

    /** @test */
    public function should_record_history_when_transitioning_to_next_state()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();

        $this->assertTrue($salesOrder->status()->stateMachine()->recordHistory());

        $this->assertEquals(1, $salesOrder->status()->history()->count());

        //Act
        $salesOrder->status()->transitionTo('approved');

        //Assert
        $salesOrder->refresh();

        $this->assertEquals(2, $salesOrder->status()->history()->count());
    }

    /** @test */
    public function can_record_history_with_custom_properties_when_transitioning_to_next_state()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();

        //Act
        $comments = $this->faker->sentence;

        $salesOrder->status()->transitionTo('approved', [
            'comments' => $comments
        ]);

        //Assert
        $salesOrder->refresh();

        $this->assertTrue($salesOrder->status()->is('approved'));

        $this->assertEquals($comments, $salesOrder->status()->getCustomProperty('comments'));
    }

    /** @test */
    public function should_call_transition_hook()
    {
        Queue::fake();

        //Arrange
        $salesOrder = factory(SalesOrder::class)->create([
            'status' => 'approved',
        ]);

        //Act
        $salesOrder->fulfillment()->transitionTo('pending');

        //Assert
        Queue::assertPushed(StartSalesOrderFulfillmentJob::class, function ($job) use ($salesOrder) {
            $this->assertEquals($salesOrder->id, $job->salesOrder->id);
            return true;
        });
    }

    /** @test */
    public function can_check_if_previous_state_was_transitioned()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();

        //Act
        $salesOrder->status()->transitionTo('approved');

        $salesOrder->status()->transitionTo('processed');

        //Assert
        $salesOrder->refresh();

        $this->assertTrue($salesOrder->status()->was('approved'));
        $this->assertTrue($salesOrder->status()->was('processed'));

        $this->assertEquals(1, $salesOrder->status()->timesWas('approved'));
        $this->assertEquals(1, $salesOrder->status()->timesWas('processed'));

        $this->assertNotNull($salesOrder->status()->whenWas('approved'));
        $this->assertNotNull($salesOrder->status()->whenWas('processed'));

        $this->assertFalse($salesOrder->status()->was('another_status'));
        $this->assertEquals(0, $salesOrder->status()->was('another_status'));
    }

    /** @test */
    public function can_record_pending_transition()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();

        //Act
        $customProperties = [
            'comments' => $this->faker->sentence,
        ];

        $salesOrder->status()->postponeTransitionTo(
            'approved',
            Carbon::tomorrow()->startOfDay(),
            $customProperties
        );

        //Assert
        $salesOrder->refresh();

        $this->assertTrue($salesOrder->status()->is('pending'));

        $this->assertTrue($salesOrder->status()->hasPendingTransitions());

        /** @var PendingTransition $pendingTransition */
        $pendingTransition = $salesOrder->status()->pendingTransitions()->first();

        $this->assertEquals('status', $pendingTransition->field);
        $this->assertEquals('pending', $pendingTransition->from);
        $this->assertEquals('approved', $pendingTransition->to);

        $this->assertEquals(Carbon::tomorrow()->startOfDay(), $pendingTransition->transition_at);

        $this->assertEquals($customProperties, $pendingTransition->custom_properties);

        $this->assertNull($pendingTransition->applied_at);

        $this->assertEquals($salesOrder->id, $pendingTransition->model->id);
    }
}
