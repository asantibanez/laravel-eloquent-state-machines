<?php


namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders;


use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;

class StatusStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        return [
            'pending' => ['approved', 'waiting'],
            'approved' => ['processed'],
            'waiting' => ['cancelled'],
        ];
    }

    public function defaultState(): ?string
    {
        return 'pending';
    }
}
