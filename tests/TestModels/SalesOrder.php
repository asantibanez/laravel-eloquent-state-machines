<?php

namespace Ashraf\EloquentStateMachine\Tests\TestModels;

use Ashraf\EloquentStateMachine\Tests\TestStateMachines\SalesOrders\FulfillmentStateMachine;
use Ashraf\EloquentStateMachine\Tests\TestStateMachines\SalesOrders\StatusStateMachine;
use Ashraf\EloquentStateMachine\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasStateMachines;

    protected $guarded = [];

    public $stateMachines = [
        'status' => StatusStateMachine::class,
        'fulfillment' => FulfillmentStateMachine::class,
    ];
}
