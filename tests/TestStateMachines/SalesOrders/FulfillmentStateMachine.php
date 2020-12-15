<?php


namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders;


use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestJobs\StartSalesOrderFulfillmentJob;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;

class FulfillmentStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return false;
    }

    public function transitions(): array
    {
        return [
            null => ['pending'],
            'pending' => ['complete', 'partial'],
            'partial' => ['complete'],
        ];
    }

    public function defaultState(): ?string
    {
        return null;
    }

    public function validatorForTransition($from, $to, $model): ?Validator
    {
        if ($from === null && $to === 'pending') {
            return ValidatorFacade::make([
                'status' => $model->status,
            ], [
                'status' => Rule::in('approved'),
            ]);
        }

        return parent::validatorForTransition($from, $to, $model);
    }

    public function transitionHooks(): array
    {
        return [
            'pending' => [
                function ($from, $model) {
                    StartSalesOrderFulfillmentJob::dispatch($model);
                },
                function ($from, $model) {
                    // Do something else
                },
            ],
        ];
    }
}
