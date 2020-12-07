<?php


namespace Asantibanez\LaravelEloquentStateMachines\StateMachines;

use Asantibanez\LaravelEloquentStateMachines\Models\StateHistory;

/**
 * Class State
 * @package Asantibanez\LaravelEloquentStateMachines\StateMachines
 * @property string $state
 * @property StateMachine $stateMachine
 */
class State
{
    public $state;
    public $stateMachine;

    public function __construct($state, $stateMachine)
    {
        $this->state = $state;
        $this->stateMachine = $stateMachine;
    }

    public function state()
    {
        return $this->state;
    }

    public function stateMachine()
    {
        return $this->stateMachine;
    }

    public function is($state)
    {
        return $this->state === $state;
    }

    public function was($state)
    {
        return $this->stateMachine->was($state);
    }

    public function timesWas($state)
    {
        return $this->stateMachine->timesWas($state);
    }

    public function whenWas($state)
    {
        return $this->stateMachine->whenWas($state);
    }

    public function snapshotWhen($state)
    {
        return $this->stateMachine->snapshotWhen($state);
    }

    public function snapshotsWhen($state)
    {
        return $this->stateMachine->snapshotsWhen($state);
    }

    public function history()
    {
        return $this->stateMachine->history();
    }

    public function canBe($state)
    {
        return $this->stateMachine->canBe($from = $this->state, $to = $state);
    }

    public function transitionTo($state, $customProperties = [])
    {
        $this->stateMachine->transitionTo($from = $this->state, $to = $state, $customProperties);
    }

    public function latest() : ?StateHistory
    {
        return $this->snapshotWhen($this->state);
    }

    public function getCustomProperty($key)
    {
        return optional($this->latest())->getCustomProperty($key);
    }

    public function allCustomProperties()
    {
        return optional($this->latest())->allCustomProperties();
    }
}
