<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\Feature;

use Asantibanez\LaravelEloquentStateMachines\Tests\TestCase;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrderWithCustomMethodName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CustomMethodNameTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function should_register_custom_method_name()
    {
        //Act
        $salesOrder = SalesOrderWithCustomMethodName::create();

        //Arrange
        $this->assertNotNull($salesOrder->custom_name());
        $this->assertNotNull($salesOrder->customName());

        $this->assertNotNull(SalesOrderWithCustomMethodName::whereHasStatus());
    }
}
