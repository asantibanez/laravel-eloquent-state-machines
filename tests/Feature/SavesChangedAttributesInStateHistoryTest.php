<?php

namespace Ashraf\EloquentStateMachine\Tests\Feature;

use Ashraf\EloquentStateMachine\Models\StateHistory;
use Ashraf\EloquentStateMachine\Tests\TestCase;
use Ashraf\EloquentStateMachine\Tests\TestModels\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SavesChangedAttributesInStateHistoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function should_save_changed_attributes_when_transitioning_state()
    {
        //Arrange
        $salesOrder = SalesOrder::create([
            'total' => 100,
            'notes' => 'some notes',
        ]);

        //Act
        $salesOrder->refresh();

        $salesOrder->total = 200;
        $salesOrder->notes = 'other text';

        $salesOrder->status()->transitionTo('approved');

        //Assert
        $salesOrder->refresh();

        /** @var StateHistory $lastStateTransition */
        $lastStateTransition = $salesOrder->status()->history()->get()->last();

        $this->assertContains('notes', $lastStateTransition->changedAttributesNames());
        $this->assertContains('total', $lastStateTransition->changedAttributesNames());
        $this->assertContains('status', $lastStateTransition->changedAttributesNames());

        $this->assertEquals('some notes', $lastStateTransition->changedAttributeOldValue('notes'));
        $this->assertEquals('other text', $lastStateTransition->changedAttributeNewValue('notes'));

        $this->assertEquals(100, $lastStateTransition->changedAttributeOldValue('total'));
        $this->assertEquals(200, $lastStateTransition->changedAttributeNewValue('total'));

        $this->assertEquals('pending', $lastStateTransition->changedAttributeOldValue('status'));
        $this->assertEquals('approved', $lastStateTransition->changedAttributeNewValue('status'));
    }

    /** @test */
    public function should_save_changed_attributes_on_initial_state()
    {
        //Act
        $salesOrder = SalesOrder::create([
            'total' => 300,
            'notes' => 'initial notes',
        ]);

        //Assert
        $salesOrder->refresh();

        /** @var StateHistory $lastStateTransition */
        $lastStateTransition = $salesOrder->status()->history()->first();

        $this->assertContains('notes', $lastStateTransition->changedAttributesNames());
        $this->assertContains('total', $lastStateTransition->changedAttributesNames());
        $this->assertContains('status', $lastStateTransition->changedAttributesNames());

        $this->assertEquals(null, $lastStateTransition->changedAttributeOldValue('notes'));
        $this->assertEquals('initial notes', $lastStateTransition->changedAttributeNewValue('notes'));

        $this->assertEquals(null, $lastStateTransition->changedAttributeOldValue('total'));
        $this->assertEquals(300, $lastStateTransition->changedAttributeNewValue('total'));

        $this->assertEquals(null, $lastStateTransition->changedAttributeOldValue('status'));
        $this->assertEquals('pending', $lastStateTransition->changedAttributeNewValue('status'));
    }
}
