<?php

namespace Ashraf\EloquentStateMachine\Tests\TestModels;

use Ashraf\EloquentStateMachine\Tests\TestStateMachines\SalesOrders\StatusFromAnyStateMachine;
use Ashraf\EloquentStateMachine\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Model;

class SalesOrderWithFromAny extends Model
{
    use HasStateMachines;

    protected $table = 'sales_orders';

    protected $guarded = [];

    public $stateMachines = [
        'status' => StatusFromAnyStateMachine::class,
    ];
}
