<?php


namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders;


use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestEnums\StatusEnum;

class StatusWithEnumCastingStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        return [
            StatusEnum::PENDING->value => [StatusEnum::APPROVED, StatusEnum::WAITING],
            StatusEnum::APPROVED->value => [StatusEnum::PROCESSED],
            StatusEnum::WAITING->value => [StatusEnum::CANCELLED],
        ];
    }

    public function defaultState(): ?string
    {
        return StatusEnum::PENDING->value;
    }
}
