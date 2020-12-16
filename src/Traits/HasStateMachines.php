<?php

namespace Asantibanez\LaravelEloquentStateMachines\Traits;

use Asantibanez\LaravelEloquentStateMachines\Models\PendingTransition;
use Asantibanez\LaravelEloquentStateMachines\Models\StateHistory;
use Asantibanez\LaravelEloquentStateMachines\StateMachines\State;
use Illuminate\Database\Eloquent\Model;
use Javoscript\MacroableModels\Facades\MacroableModels;

/**
 * Trait HasStateMachines
 * @package Asantibanez\LaravelEloquentStateMachines\Traits
 * @property array $stateMachines
 */
trait HasStateMachines
{
    public static function bootHasStateMachines()
    {
        $model = new static();

        collect($model->stateMachines)
            ->each(function ($_, $field) use ($model) {
                MacroableModels::addMacro(static::class, "$field", function () use ($field) {
                    $stateMachine = new $this->stateMachines[$field]($field, $this);
                    return new State($this->{$stateMachine->field}, $stateMachine);
                });
            });

        self::creating(function (Model $model) {
            $model->initStateMachines();
        });

        self::created(function (Model $model) {
            collect($model->stateMachines)
                ->each(function ($_, $field) use ($model) {
                    $currentState = $model->$field;
                    $stateMachine = $model->$field()->stateMachine();

                    if ($currentState === null) {
                        return;
                    }

                    if (!$stateMachine->recordHistory()) {
                        return;
                    }

                    $responsible = auth()->user();

                    $model->recordState($field, null, $currentState, [], $responsible);
                });
        });
    }

    public function initStateMachines()
    {
        collect($this->stateMachines)
            ->each(function ($stateMachineClass, $field) {
                $stateMachine = new $stateMachineClass($field, $this);

                $this->{$field} = $this->{$field} ?? $stateMachine->defaultState();
            });
    }

    public function stateHistory()
    {
        return $this->morphMany(StateHistory::class, 'model');
    }

    public function pendingTransitions()
    {
        return $this->morphMany(PendingTransition::class, 'model');
    }

    public function recordState($field, $from, $to, $customProperties = [], $responsible = null)
    {
        $stateHistory = StateHistory::make([
            'field' => $field,
            'from' => $from,
            'to' => $to,
            'custom_properties' => $customProperties,
        ]);

        if ($responsible !== null) {
            $stateHistory->responsible()->associate($responsible);
        }

        $this->stateHistory()->save($stateHistory);
    }

    public function recordPendingTransition($field, $from, $to, $when, $customProperties = [], $responsible = null) : PendingTransition
    {
        $pendingTransition = PendingTransition::make([
            'field' => $field,
            'from' => $from,
            'to' => $to,
            'transition_at' => $when,
            'custom_properties' => $customProperties
        ]);

        if ($responsible !== null) {
            $pendingTransition->responsible()->associate($responsible);
        }

        return $this->pendingTransitions()->save($pendingTransition);
    }
}
