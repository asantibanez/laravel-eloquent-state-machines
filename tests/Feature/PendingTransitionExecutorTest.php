<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\Feature;

use Asantibanez\LaravelEloquentStateMachines\Exceptions\InvalidStartingStateException;
use Asantibanez\LaravelEloquentStateMachines\Jobs\PendingTransitionExecutor;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestCase;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Throwable;

class PendingTransitionExecutorTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function should_apply_pending_transition()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();

        $salesOrder->status()->postponeTransitionTo('approved', Carbon::now());

        $this->assertTrue($salesOrder->status()->is('pending'));

        $this->assertTrue($salesOrder->status()->hasPendingTransitions());

        //Act
        $pendingTransition = $salesOrder->status()->pendingTransitions()->first();

        PendingTransitionExecutor::dispatch($pendingTransition);

        //Assert
        $salesOrder->refresh();

        $this->assertTrue($salesOrder->status()->is('approved'));

        $this->assertFalse($salesOrder->status()->hasPendingTransitions());
    }

    /** @test */
    public function should_throw_exception_if_starting_transition_is_not_the_same_as_when_postponed()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();

        $salesOrder->status()->postponeTransitionTo('approved', Carbon::now());

        //Manually update state
        $salesOrder->update(['status' => 'processed']);
        $this->assertTrue($salesOrder->status()->is('processed'));

        $this->assertTrue($salesOrder->status()->hasPendingTransitions());

        //Act
        $pendingTransition = $salesOrder->status()->pendingTransitions()->first();

        try {
            PendingTransitionExecutor::dispatch($pendingTransition);
            $this->fail('Should have thrown exception');
        } catch (Throwable $exception) {
            //Assert
            $this->assertTrue($exception instanceof InvalidStartingStateException);
        }
    }
}
