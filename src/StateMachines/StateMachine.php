<?php


namespace Asantibanez\LaravelEloquentStateMachines\StateMachines;


use Asantibanez\LaravelEloquentStateMachines\Exceptions\TransitionNotAllowedException;
use Asantibanez\LaravelEloquentStateMachines\Models\PendingTransition;
use Asantibanez\LaravelEloquentStateMachines\Models\StateHistory;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
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

    public function currentState()
    {
        $field = $this->field;

        return $this->model->$field;
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
     * @param null|mixed $responsible
     * @throws TransitionNotAllowedException
     * @throws ValidationException
     */
    public function transitionTo($from, $to, $customProperties = [], $responsible = null)
    {
        if ($to === $this->currentState()) {
            return;
        }

        if (!$this->canBe($from, $to)) {
            throw new TransitionNotAllowedException();
        }

        $validator = $this->validatorForTransition($from, $to, $this->model);
        if ($validator !== null && $validator->fails()) {
            throw new ValidationException($validator);
        }

        $beforeTransitionHooks = $this->beforeTransitionHooks()[$from] ?? [];

        collect($beforeTransitionHooks)
            ->each(function ($callable) use ($to) {
                $callable($to, $this->model);
            });

        $field = $this->field;
        $this->model->$field = $to;

        $changedAttributes = $this->model->getChangedAttributes();

        $this->model->save();

        if ($this->recordHistory()) {
            $responsible = $responsible ?? auth()->user();

            $this->model->recordState($field, $from, $to, $customProperties, $responsible, $changedAttributes);
        }

        $afterTransitionHooks = $this->afterTransitionHooks()[$to] ?? [];

        collect($afterTransitionHooks)
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
     * @param null $responsible
     * @return null|PendingTransition
     * @throws TransitionNotAllowedException
     */
    public function postponeTransitionTo($from, $to, Carbon $when, $customProperties = [], $responsible = null) : ?PendingTransition
    {
        if ($to === $this->currentState()) {
            return null;
        }

        if (!$this->canBe($from, $to)) {
            throw new TransitionNotAllowedException();
        }

        $responsible = $responsible ?? auth()->user();

        return $this->model->recordPendingTransition(
            $this->field,
            $from,
            $to,
            $when,
            $customProperties,
            $responsible
        );
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

    public function afterTransitionHooks() : array
    {
        return [];
    }

    public function beforeTransitionHooks() : array {
        return [];
    }
}
