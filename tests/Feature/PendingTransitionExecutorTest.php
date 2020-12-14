<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\Feature;

use Asantibanez\LaravelEloquentStateMachines\Jobs\PendingTransitionExecutor;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestCase;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;

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

        Queue::after(function (JobProcessed $event) {
            $this->assertFalse($event->job->hasFailed());
        });

        //Act
        $pendingTransition = $salesOrder->status()->pendingTransitions()->first();

        PendingTransitionExecutor::dispatch($pendingTransition);

        //Assert
        $salesOrder->refresh();

        $this->assertTrue($salesOrder->status()->is('approved'));

        $this->assertFalse($salesOrder->status()->hasPendingTransitions());
    }

    /** @test */
    public function should_fail_job_automatically_if_starting_transition_is_not_the_same_as_when_postponed()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();

        $salesOrder->status()->postponeTransitionTo('approved', Carbon::now());

        //Manually update state
        $salesOrder->update(['status' => 'processed']);
        $this->assertTrue($salesOrder->status()->is('processed'));

        $this->assertTrue($salesOrder->status()->hasPendingTransitions());

        Queue::after(function (JobProcessed $event) {
            $this->assertTrue($event->job->hasFailed());
        });

        //Act
        $pendingTransition = $salesOrder->status()->pendingTransitions()->first();

        PendingTransitionExecutor::dispatch($pendingTransition);
    }
}
