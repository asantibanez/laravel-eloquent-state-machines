<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestModels;

use Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders\StatusWithCustomMethodNameStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Model;

class SalesOrderWithCustomMethodName extends Model
{
    use HasStateMachines;

    protected $table = 'sales_orders';

    protected $guarded = [];

    public $stateMachines = [
        'status' => StatusWithCustomMethodNameStateMachine::class,
    ];
}
