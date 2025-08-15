<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\Unit;

use Asantibanez\LaravelEloquentStateMachines\Tests\TestCase;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrder;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrderWithAnyToAny;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrderWithFromAny;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestModels\SalesOrderWithToAny;

class StateCanBeTest extends TestCase
{
    public function test_can_be(): void
    {
        // [GIVEN] allowed [specific => specific] transition
        $model = new SalesOrder(['status' => 'pending']);
        // [WHEN] check transition availability
        $result = $model->status()->canBe('approved');
        // [THEN] allow transition
        $this->assertTrue($result);
    }

    public function test_can_be_from_any(): void
    {
        // [GIVEN] allowed [* => specific] transition
        $model = new SalesOrderWithFromAny(['status' => 'pending']);
        // [WHEN] check transition availability
        $result = $model->status()->canBe('approved');
        // [THEN] allow transition
        $this->assertTrue($result);
    }

    public function test_can_be_to_any(): void
    {
        // [GIVEN] allowed [specific => *] transition
        $model = new SalesOrderWithToAny(['status' => 'pending']);
        // [WHEN] check transition availability
        $result = $model->status()->canBe('approved');
        // [THEN] allow transition
        $this->assertTrue($result);
    }

    public function test_can_be_from_any_to_any(): void
    {
        // [GIVEN] allowed [* => specific] transition
        $model = new SalesOrderWithAnyToAny(['status' => 'pending']);
        // [WHEN] check transition availability
        $result = $model->status()->canBe('approved');
        // [THEN] allow transition
        $this->assertTrue($result);
    }

    public function test_can_be_false(): void
    {
        // [GIVEN] forbidden [specific => specific] transition
        $model = new SalesOrder(['status' => 'approved']);
        // [WHEN] check transition availability
        $result = $model->status()->canBe('pending');
        // [THEN] deny transition
        $this->assertFalse($result);
    }

    public function test_can_be_from_any_false(): void
    {
        // [GIVEN] forbidden [* => specific] transition
        $model = new SalesOrderWithFromAny(['status' => 'pending']);
        // [WHEN] check transition availability
        $result = $model->status()->canBe('new');
        // [THEN] deny transition
        $this->assertFalse($result);
    }

    public function test_can_be_to_any_false(): void
    {
        // [GIVEN] forbidden [specific => *] transition
        $model = new SalesOrderWithToAny(['status' => 'new']);
        // [WHEN] check transition availability
        $result = $model->status()->canBe('pending');
        // [THEN] deny transition
        $this->assertFalse($result);
    }
}
