<?php


namespace Ashraf\EloquentStateMachine\StateMachines;


use Ashraf\EloquentStateMachine\Exceptions\TransitionNotAllowedException;
use Ashraf\EloquentStateMachine\Models\StateHistory;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Auth\Authenticatable;

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

    public function whenWas($state): ?Carbon
    {
        $stateHistory = $this->snapshotWhen($state);

        if ($stateHistory === null) {
            return null;
        }

        return $stateHistory->created_at;
    }

    public function snapshotWhen($state): ?StateHistory
    {
        return $this->history()->to($state)->latest('id')->first();
    }

    public function snapshotsWhen($state): Collection
    {
        return $this->history()->to($state)->get();
    }


    /**
     * check if the model can be transitioned from $from to $to
     * 
     * @param string $from
     * @param string $to
     * @param null|mixed $who
     */
    public function canBe($from, $to, $who = null): bool
    {
        $availableTransitions = $this->transitions()[$from] ?? [];

        return collect(array_keys($availableTransitions))->contains($to)
            && $this->executeTransitionValidation($from, $to, $who);
    }

    /**
     * Execute the transition validation callback if exists
     * 
     * @param $from
     * @param $to
     * @param null|mixed $who
     * @return bool
     */
    public function executeTransitionValidation($from, $to, $who = null): bool
    {
        $target = $this->transitions()[$from][$to];
        return is_callable($target) ? $target($this->model, $who) : true;
    }

    /**
     * @param $from
     * @param $to
     * @param array $customProperties
     * @param null|mixed $responsible
     * @throws TransitionNotAllowedException
     * @throws \League\Config\Exception\ValidationException
     */
    public function transitionTo($from, $to, $customProperties = [], $responsible = null)
    {
        if ($to === $this->currentState()) {
            return;
        }

        $responsible = $responsible ?? auth()->user();



        if (
            !$this->canBe($from, $to, $responsible)
            && !$this->canBe($from, '*', $responsible)
            && !$this->canBe('*', $to, $responsible)
            && !$this->canBe('*', '*', $responsible)
        ) {
            throw new TransitionNotAllowedException($from, $to, get_class($this->model));
        }

        $validator = $this->validatorForTransition($from, $to, $this->model);
        if ($validator !== null && $validator->fails()) {
            throw new ValidationException($validator);
        }


        collect($this->beforeTransitionHooks()[$from] ?? [])
            ->each(function ($callable) use ($to) {
                $callable($to, $this->model);
            });


        // save changes
        $field = $this->field;
        $this->model->$field = $to;
        $changedAttributes = $this->model->getChangedAttributes();
        $this->model->save();

        if ($this->recordHistory()) {
            $this->model->recordState($field, $from, $to, $customProperties, $responsible, $changedAttributes);
        }


        collect($this->afterTransitionHooks()[$to] ?? [])
            ->each(function ($callable) use ($from) {
                $callable($from, $this->model);
            });
    }


    abstract public function transitions(): array;

    abstract public function defaultState(): ?string;

    abstract public function recordHistory(): bool;

    public function validatorForTransition($from, $to, $model): ?Validator
    {
        return null;
    }

    public function afterTransitionHooks(): array
    {
        return [];
    }

    public function beforeTransitionHooks(): array
    {
        return [];
    }


    /**
     * @param null|Authenticatable $responsible
     * @return array<string>
     */
    public function availableTransitions($responsible = null): array
    {
        $currentState = $this->currentState();

        $availableTransitions = $this->transitions()[$currentState] ?? [];

        return collect($availableTransitions)
            ->map(function ($target, $transition) use ($currentState, $responsible) {
                return [
                    'transition' => $transition,
                    'target' => $target,
                    'can' => $this->canBe($currentState, $transition, $responsible ?? auth()->user()),
                ];
            })
            ->filter(fn ($status) => $status['can'])->keys()->toArray();
    }
}
