<?php


namespace Ashraf\EloquentStateMachine\Exceptions;


use Exception;

class InvalidStartingStateException extends Exception
{
    public function __construct($expectedState, $actualState)
    {
        $message = "Expected: $expectedState. Actual: $actualState";

        parent::__construct($message);
    }
}
