<?php


namespace Asantibanez\LaravelEloquentStateMachines\Jobs;


use Asantibanez\LaravelEloquentStateMachines\Exceptions\InvalidStartingStateException;
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

    /**
     * @throws InvalidStartingStateException
     */
    public function handle()
    {
        $field = $this->pendingTransition->field;
        $model = $this->pendingTransition->model;
        $from = $this->pendingTransition->from;
        $to = $this->pendingTransition->to;
        $customProperties = $this->pendingTransition->custom_properties;

        if ($model->$field()->isNot($from)) {
            throw new InvalidStartingStateException();
        }

        $model->$field()->transitionTo($to, $customProperties);
    }
}
