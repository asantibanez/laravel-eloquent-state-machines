<?php


namespace Asantibanez\LaravelEloquentStateMachines\StateMachines;


use Asantibanez\LaravelEloquentStateMachines\Exceptions\TransitionNotAllowedException;
use Illuminate\Contracts\Validation\Validator;
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

    public function snapshotWhen($state)
    {
        return $this->model->stateHistory()
            ->forField($this->field)
            ->to($state)
            ->latest('id')
            ->first();
    }

    public function snapshotsWhen($state)
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

        $transitionHooks = data_get($this->transitionHooks(), $to, []);

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
