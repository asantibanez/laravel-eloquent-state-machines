<?php


namespace Asantibanez\LaravelEloquentStateMachines\StateMachines;


use Asantibanez\LaravelEloquentStateMachines\Exceptions\TransitionNotAllowedException;
use Asantibanez\LaravelEloquentStateMachines\Models\PendingTransition;
use Asantibanez\LaravelEloquentStateMachines\Models\StateHistory;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

abstract class StateMachine
{
    public $field;
    public $model;

    public function __construct($field, &$model)
    {
        $this->field = $field;

        $this->model = $model;
    }

    public function history()
    {
        return $this->model->stateHistory()->forField($this->field);
    }

    public function was($state)
    {
        return $this->history()->to($state)->exists();
    }

    public function timesWas($state)
    {
        return $this->history()->to($state)->count();
    }

    public function whenWas($state) : ?Carbon
    {
        $stateHistory = $this->snapshotWhen($state);

        if ($stateHistory === null) {
            return null;
        }

        return $stateHistory->created_at;
    }

    public function snapshotWhen($state) : ?StateHistory
    {
        return $this->history()->to($state)->latest('id')->first();
    }

    public function snapshotsWhen($state) : Collection
    {
        return $this->history()->to($state)->get();
    }

    public function canBe($from, $to)
    {
        $availableTransitions = $this->transitions()[$from] ?? [];

        return collect($availableTransitions)->contains($to);
    }

    public function pendingTransitions()
    {
        return $this->model->pendingTransitions()->forField($this->field);
    }

    public function hasPendingTransitions()
    {
        return $this->pendingTransitions()->notApplied()->exists();
    }

    /**
     * @param $from
     * @param $to
     * @param array $customProperties
     * @throws TransitionNotAllowedException
     * @throws ValidationException
     */
    public function transitionTo($from, $to, $customProperties = [])
    {
        if (!$this->canBe($from, $to)) {
            throw new TransitionNotAllowedException();
        }

        $validator = $this->validatorForTransition($from, $to, $this->model);
        if ($validator !== null && $validator->fails()) {
            throw new ValidationException($validator);
        }

        $field = $this->field;
        $this->model->$field = $to;
        $this->model->save();

        if ($this->recordHistory()) {
            $this->model->recordState($field, $from, $to, $customProperties);
        }

        $transitionHooks = $this->transitionHooks()[$to] ?? [];

        collect($transitionHooks)
            ->each(function ($callable) use ($from) {
                $callable($from, $this->model);
            });

        $this->cancelAllPendingTransitions();
    }

    /**
     * @param $from
     * @param $to
     * @param Carbon $when
     * @param array $customProperties
     * @return PendingTransition
     */
    public function postponeTransitionTo($from, $to, Carbon $when, $customProperties = []) : PendingTransition
    {
        return $this->model->recordPendingTransition($this->field, $from, $to, $when, $customProperties);
    }

    public function cancelAllPendingTransitions()
    {
        $this->pendingTransitions()->delete();
    }

    abstract public function transitions() : array;

    abstract public function defaultState() : ?string;

    abstract public function recordHistory() : bool;

    public function validatorForTransition($from, $to, $model): ?Validator
    {
        return null;
    }

    public function transitionHooks() : array {
        return [];
    }
}
