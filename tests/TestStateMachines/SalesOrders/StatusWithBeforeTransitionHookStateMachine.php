<?php


namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestStateMachines\SalesOrders;


use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;
use Asantibanez\LaravelEloquentStateMachines\Tests\TestJobs\BeforeTransitionJob;

class StatusWithBeforeTransitionHookStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        return [
            'pending' => ['approved'],
            'approved' => ['processed'],
        ];
    }

    public function defaultState(): ?string
    {
        return 'pending';
    }

    public function beforeTransitionHooks(): array
    {
        return [
            'pending' => [
                function($to, $model) {
                    $model->total = 100;
                },
                function($to, $model) {
                    $model->notes = 'Notes updated';
                },
                function($to, $model, $customProperties) {
                    if (isset($customProperties['custom'])) {
                        $model->custom = $customProperties['custom'];
                    }
                },
                function ($to, $model) {
                    BeforeTransitionJob::dispatch();
                }
            ]
        ];
    }
}
