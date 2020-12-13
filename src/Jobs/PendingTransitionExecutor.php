<?php


namespace Asantibanez\LaravelEloquentStateMachines\Jobs;


use Asantibanez\LaravelEloquentStateMachines\Models\PendingTransition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PendingTransitionExecutor implements ShouldQueue
{
    use InteractsWithQueue, Queueable, Dispatchable, SerializesModels;

    public $pendingTransition;

    public function __construct(PendingTransition $pendingTransition)
    {
        $this->pendingTransition = $pendingTransition;
    }

    public function handle()
    {
        $field = $this->pendingTransition->field;
        $model = $this->pendingTransition->model;
        $to = $this->pendingTransition->to;
        $customProperties = $this->pendingTransition->custom_properties;

        $model->$field()->transitionTo($to, $customProperties);
    }
}
