<?php


namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders;


use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;

class StatusToAnyStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return false;
    }

    public function transitions(): array
    {
        return [
            'pending' => '*',
        ];
    }

    public function defaultState(): ?string
    {
        return 'pending';
    }
}
