<?php

namespace Ashraf\EloquentStateMachine\Tests\Feature;

use Ashraf\EloquentStateMachine\Tests\TestCase;
use Ashraf\EloquentStateMachine\Tests\TestModels\SalesManager;
use Ashraf\EloquentStateMachine\Tests\TestModels\SalesOrder;
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
            ->get();

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
            ->get();

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
            ->get();

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
            ->get();

        //Assert
        $this->assertEquals(1, $salesOrders->count());

        $this->assertEquals($salesOrder->id, $salesOrders->first()->id);
    }

    /** @test */
    public function can_get_models_with_an_array_of_transition_to_states()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();
        $salesOrder->status()->transitionTo('approved');
        $salesOrder->status()->transitionTo('processed');

        $salesOrder2 = factory(SalesOrder::class)->create();
        $salesOrder2->status()->transitionTo('waiting');
        $salesOrder2->status()->transitionTo('cancelled');

        $anotherSalesOrder = factory(SalesOrder::class)->create();
        $anotherSalesOrder->status()->transitionTo('approved');

        //Act
        $salesOrders = SalesOrder::with([])
            ->whereHasStatus(function ($query) {
                $query->transitionedTo(['processed', 'cancelled']);
            })
            ->get();

        //Assert
        $this->assertEquals(2, $salesOrders->count());

        $this->assertEquals($salesOrder->id, $salesOrders[0]->id);
        $this->assertEquals($salesOrder2->id, $salesOrders[1]->id);
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
            ->get();

        //Assert
        $this->assertEquals(1, $salesOrders->count());

        $this->assertEquals($salesOrder->id, $salesOrders->first()->id);
    }

    /** @test */
    public function can_get_models_with_an_array_of_transition_from_states()
    {
        //Arrange
        $salesOrder = factory(SalesOrder::class)->create();
        $salesOrder->status()->transitionTo('approved');
        $salesOrder->status()->transitionTo('processed');

        $anotherSalesOrder = factory(SalesOrder::class)->create();
        $anotherSalesOrder->status()->transitionTo('approved');

        $anotherSalesOrder2 = factory(SalesOrder::class)->create();
        $anotherSalesOrder2->status()->transitionTo('waiting');
        $anotherSalesOrder2->status()->transitionTo('cancelled');

        //Act
        $salesOrders = SalesOrder::with([])
            ->whereHasStatus(function ($query) {
                $query->transitionedFrom(['approved', 'waiting']);
            })
            ->get();

        //Assert
        $this->assertEquals(2, $salesOrders->count());

        $this->assertEquals($salesOrder->id, $salesOrders[0]->id);
        $this->assertEquals($anotherSalesOrder2->id, $salesOrders[1]->id);
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
            ->get();

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
            ->get();

        //Assert
        $this->assertEquals(1, $salesOrders->count());

        $this->assertEquals($salesOrder->id, $salesOrders->first()->id);
    }
}
