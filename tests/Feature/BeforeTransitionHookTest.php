<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\Feature;

use Asantibanez\LaravelEloquentStateMachines\Tests\TestCase;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestJobs\BeforeTransitionJob;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrderWithBeforeTransitionHook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;

class BeforeTransitionHookTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function should_call_before_transition_hooks()
    {
        //Arrange
        Queue::fake();

        $salesOrder = SalesOrderWithBeforeTransitionHook::create();

        $this->assertNull($salesOrder->total);
        $this->assertNull($salesOrder->notes);

        //Act
        $salesOrder->status()->transitionTo('approved');

        //Assert
        $salesOrder->refresh();

        $this->assertEquals(100, $salesOrder->total);
        $this->assertEquals('Notes updated', $salesOrder->notes);

        Queue::assertPushed(BeforeTransitionJob::class);
    }

    /** @test */
    public function should_call_before_transition_hooks_with_custom_properties()
    {
        //Arrange
        Queue::fake();

        $salesOrder = SalesOrderWithBeforeTransitionHook::create();

        $this->assertNull($salesOrder->total);
        $this->assertNull($salesOrder->notes);
        $this->assertNull($salesOrder->custom);

        //Act
        $salesOrder->status()->transitionTo('approved', [
            'custom' => 'property',
        ]);

        //Assert
        $salesOrder->refresh();

        $this->assertEquals(100, $salesOrder->total);
        $this->assertEquals('Notes updated', $salesOrder->notes);
        $this->assertEquals('property', $salesOrder->custom);

        Queue::assertPushed(BeforeTransitionJob::class);
    }

    /** @test */
    public function should_not_call_before_transition_hooks_if_not_defined()
    {
        //Arrange
        Queue::fake();

        $salesOrder = SalesOrderWithBeforeTransitionHook::create([
            'status' => 'approved'
        ]);

        $this->assertNull($salesOrder->total);
        $this->assertNull($salesOrder->notes);

        //Act
        $salesOrder->status()->transitionTo('processed');

        //Assert
        $salesOrder->refresh();

        $this->assertNull($salesOrder->total);
        $this->assertNull($salesOrder->notes);

        Queue::assertNotPushed(BeforeTransitionJob::class);
    }
}
