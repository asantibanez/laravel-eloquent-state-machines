<?php


namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders;


use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;

class StatusWithCustomMethodNameStateMachine extends StateMachine
{
    public static function methodName()
    {
        return 'custom_name';
    }

    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        return [
            'pending' => ['approved'],
        ];
    }

    public function defaultState(): ?string
    {
        return 'pending';
    }
}
