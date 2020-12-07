<?php


namespace Asantibanez\LaravelEloquentStateMachines\StateMachines;


use Asantibanez\LaravelEloquentStateMachines\Exceptions\TransitionNotAllowedException;
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

    public function was($state)
    {
        return $this->model->stateHistory()
            ->forField($this->field)
            ->to($state)
            ->exists();
    }

    public function timesWas($state)
    {
        return $this->model->stateHistory()
            ->forField($this->field)
            ->to($state)
            ->count();
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
        return $this->model->stateHistory()
            ->forField($this->field)
            ->to($state)
            ->latest('id')
            ->first();
    }

    public function snapshotsWhen($state) : Collection
    {
        return $this->model->stateHistory()
            ->forField($this->field)
            ->to($state)
            ->get();
    }

    public function history()
    {
        return $this->model->stateHistory()
            ->forField($this->field);
    }

    public function canBe($from, $to)
    {
        $availableTransitions = $this->transitions()[$from] ?? [];

        return collect($availableTransitions)->contains($to);
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
    }

    abstract public function recordHistory() : bool;

    abstract public function transitions() : array;

    abstract public function defaultState() : ?string;

    public function validatorForTransition($from, $to, $model): ?Validator
    {
        return null;
    }

    public function transitionHooks() : array {
        return [];
    }
}
