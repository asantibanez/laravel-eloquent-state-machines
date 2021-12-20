<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestModels;

use Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders\StatusToAnyStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
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
