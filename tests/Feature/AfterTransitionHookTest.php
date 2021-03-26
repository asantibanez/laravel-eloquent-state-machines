<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\Feature;

use Asantibanez\LaravelEloquentStateMachines\Tests\TestCase;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestJobs\AfterTransitionJob;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrderWithAfterTransitionHook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;

class AfterTransitionHookTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function should_call_after_transition_hooks()
    {
        //Arrange
        Queue::fake();

        $salesOrder = SalesOrderWithAfterTransitionHook::create([
            'total' => 100,
            'notes' => 'before',
        ]);

        //Act
        $salesOrder->status()->transitionTo('approved');

        //Assert
        $salesOrder->refresh();

        $this->assertEquals(200, $salesOrder->total);
        $this->assertEquals('after', $salesOrder->notes);

        Queue::assertPushed(AfterTransitionJob::class);
    }

    /** @test */
    public function should_call_after_transition_hooks_with_custom_properties()
    {
        //Arrange
        Queue::fake();

        $salesOrder = SalesOrderWithAfterTransitionHook::create([
            'total' => 100,
            'notes' => 'before',
            'custom' => 'no'
        ]);

        //Act
        $salesOrder->status()->transitionTo('approved', [
            'custom' => 'yes',
        ]);

        //Assert
        $salesOrder->refresh();

        $this->assertEquals(200, $salesOrder->total);
        $this->assertEquals('after', $salesOrder->notes);
        $this->assertEquals('yes', $salesOrder->custom);

        Queue::assertPushed(AfterTransitionJob::class);
    }

    /** @test */
    public function should_not_call_after_transition_hooks_if_not_defined()
    {
        //Arrange
        Queue::fake();

        $salesOrder = SalesOrderWithAfterTransitionHook::create([
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

        Queue::assertNotPushed(AfterTransitionJob::class);
    }
}
