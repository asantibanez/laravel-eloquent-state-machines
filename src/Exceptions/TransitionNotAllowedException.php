<?php


namespace Ashraf\EloquentStateMachine\Exceptions;


use Exception;

class TransitionNotAllowedException extends Exception
{
    protected $from;
    protected $to;
    protected $model;

    public function __construct($from, $to, $model)
    {
        $this->from = $from;
        $this->to = $to;
        $this->model = $model;

        parent::__construct("Transition from '$from' to '$to' is not allowed for model '$model'", 422);
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getModel()
    {
        return $this->model;
    }
}
