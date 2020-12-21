<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\Feature;

use Asantibanez\LaravelEloquentStateMachines\Tests\TestCase;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesManager;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class QueryScopesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function can_get_models_with_transition_responsible_model()
    {
        //Arrange
        $salesManager = factory(SalesManager::class)->create();

        $anotherSalesManager = factory(SalesManager::class)->create();

        factory(SalesOrder::class)->create()->status()->transitionTo('approved', [], $salesManager);
        factory(SalesOrder::class)->create()->status()->transitionTo('approved', [], $salesManager);
        factory(SalesOrder::class)->create()->status()->transitionTo('approved', [], $anotherSalesManager);

        //Act
        $salesOrders = SalesOrder::with([])
            ->whereHasStatus(function ($query) use ($salesManager) {
                $query->withResponsible($salesManager);
            })
            ->get()
        ;

        //Assert
        $this->assertEquals(2, $salesOrders->count());

        $salesOrders->each(function (SalesOrder $salesOrder) use ($salesManager) {
            $this->assertEquals($salesManager->id, $salesOrder->status()->snapshotWhen('approved')->responsible->id);
        });
    }

    /** @test */
    public function can_get_models_with_transition_responsible_id()
    {
        //Arrange
        $salesManager = factory(SalesManager::class)->create();

        $anotherSalesManager = factory(SalesManager::class)->create();

        factory(SalesOrder::class)->create()->status()->transitionTo('approved', [], $salesManager);
        factory(SalesOrder::class)->create()->status()->transitionTo('approved', [], $anotherSalesManager);

        //Act
        $salesOrders = SalesOrder::with([])
            ->whereHasStatus(function ($query) use ($salesManager) {
                $query->withResponsible($salesManager->id);
            })
            ->get()
        ;

        //Assert
        $this->assertEquals(1, $salesOrders->count());
    }

    /** @test */
    public function can_get_models_with_specific_transition()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();
        $salesOrder->status()->transitionTo('approved');
        $salesOrder->status()->transitionTo('processed');

        $anotherSalesOrder = factory(SalesOrder::class)->create();
        $anotherSalesOrder->status()->transitionTo('approved');

        //Act
        $salesOrders = SalesOrder::with([])
            ->whereHasStatus(function ($query) {
                $query->withTransition('approved', 'processed');
            })
            ->get()
        ;

        //Assert
        $this->assertEquals(1, $salesOrders->count());

        $this->assertEquals($salesOrder->id, $salesOrders->first()->id);
    }

    /** @test */
    public function can_get_models_with_specific_transition_to_state()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();
        $salesOrder->status()->transitionTo('approved');
        $salesOrder->status()->transitionTo('processed');

        $anotherSalesOrder = factory(SalesOrder::class)->create();
        $anotherSalesOrder->status()->transitionTo('approved');

        //Act
        $salesOrders = SalesOrder::with([])
            ->whereHasStatus(function ($query) {
                $query->transitionedTo('processed');
            })
            ->get()
        ;

        //Assert
        $this->assertEquals(1, $salesOrders->count());

        $this->assertEquals($salesOrder->id, $salesOrders->first()->id);
    }

    /** @test */
    public function can_get_models_with_specific_transition_from_state()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();
        $salesOrder->status()->transitionTo('approved');
        $salesOrder->status()->transitionTo('processed');

        $anotherSalesOrder = factory(SalesOrder::class)->create();
        $anotherSalesOrder->status()->transitionTo('approved');

        //Act
        $salesOrders = SalesOrder::with([])
            ->whereHasStatus(function ($query) {
                $query->transitionedFrom('approved');
            })
            ->get()
        ;

        //Assert
        $this->assertEquals(1, $salesOrders->count());

        $this->assertEquals($salesOrder->id, $salesOrders->first()->id);
    }

    /** @test */
    public function can_get_models_with_specific_transition_custom_property()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();
        $salesOrder->status()->transitionTo('approved', ['comments' => 'Checked']);

        $anotherSalesOrder = factory(SalesOrder::class)->create();
        $anotherSalesOrder->status()->transitionTo('approved', ['comments' => 'Needs further revision']);

        //Act
        $salesOrders = SalesOrder::with([])
            ->whereHasStatus(function ($query) {
                $query->withCustomProperty('comments', 'like', '%Check%');
            })
            ->get()
        ;

        //Assert
        $this->assertEquals(1, $salesOrders->count());

        $this->assertEquals($salesOrder->id, $salesOrders->first()->id);
    }

    /** @test */
    public function can_get_models_using_multiple_state_machines_transitions()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();
        $salesOrder->status()->transitionTo('approved');
        $salesOrder->status()->transitionTo('processed');

        $anotherSalesOrder = factory(SalesOrder::class)->create();
        $anotherSalesOrder->status()->transitionTo('approved');

        //Act


        $salesOrders = SalesOrder::with([])
            ->whereHasStatus(function ($query) {
                $query->transitionedTo('approved');
            })
            ->whereHasStatus(function ($query) {
                $query->transitionedTo('processed');
            })
            ->get()
        ;

        //Assert
        $this->assertEquals(1, $salesOrders->count());

        $this->assertEquals($salesOrder->id, $salesOrders->first()->id);
    }
}
