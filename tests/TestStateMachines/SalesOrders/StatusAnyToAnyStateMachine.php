<?php


namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders;


use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;

class StatusAnyToAnyStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return false;
    }

    public function transitions(): array
    {
        return [
            '*' => '*',
        ];
    }

    public function defaultState(): ?string
    {
        return 'new';
    }
}
