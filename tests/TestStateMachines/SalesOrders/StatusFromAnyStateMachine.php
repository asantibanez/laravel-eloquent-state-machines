<?php


namespace Ashraf\EloquentStateMachine\Tests\TestStateMachines\SalesOrders;


use Ashraf\EloquentStateMachine\StateMachines\StateMachine;

class StatusFromAnyStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return false;
    }

    public function transitions(): array
    {
        return [
            '*' => ['pending', 'approved', 'processed'],
        ];
    }

    public function defaultState(): ?string
    {
        return 'pending';
    }
}
