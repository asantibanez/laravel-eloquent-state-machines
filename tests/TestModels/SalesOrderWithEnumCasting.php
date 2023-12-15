<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestModels;

use Asantibanez\LaravelEloquentStateMachines\Tests\TestEnums\StatusEnum;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders\StatusWithEnumCastingStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Model;

class SalesOrderWithEnumCasting extends Model
{
    use HasStateMachines;

    protected $table = 'sales_orders';

    protected $guarded = [];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public $stateMachines = [
        'status' => StatusWithEnumCastingStateMachine::class,
    ];
}
