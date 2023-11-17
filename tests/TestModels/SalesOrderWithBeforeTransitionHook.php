<?php

namespace Ashraf\EloquentStateMachine\Tests\TestModels;

use Ashraf\EloquentStateMachine\Tests\TestStateMachines\SalesOrders\StatusWithBeforeTransitionHookStateMachine;
use Ashraf\EloquentStateMachine\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Model;

class SalesOrderWithBeforeTransitionHook extends Model
{
    use HasStateMachines;

    protected $table = 'sales_orders';

    protected $guarded = [];

    public $stateMachines = [
        'status' => StatusWithBeforeTransitionHookStateMachine::class,
    ];
}
