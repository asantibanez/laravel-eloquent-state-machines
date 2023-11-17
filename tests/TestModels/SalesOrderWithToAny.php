<?php

namespace Ashraf\EloquentStateMachine\Tests\TestModels;

use Ashraf\EloquentStateMachine\Tests\TestStateMachines\SalesOrders\StatusToAnyStateMachine;
use Ashraf\EloquentStateMachine\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Model;

class SalesOrderWithToAny extends Model
{
    use HasStateMachines;

    protected $table = 'sales_orders';

    protected $guarded = [];

    public $stateMachines = [
        'status' => StatusToAnyStateMachine::class,
    ];
}
