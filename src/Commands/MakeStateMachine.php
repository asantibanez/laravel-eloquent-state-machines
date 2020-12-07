<?php


namespace Asantibanez\LaravelEloquentStateMachines\Commands;


use Illuminate\Console\GeneratorCommand;

class MakeStateMachine extends GeneratorCommand
{
    protected $signature = 'make:state-machine {name}';

    protected $description = 'Create a new state machine';

    protected $type = 'StateMachine';

    protected function getStub()
    {
        return __DIR__ . '/stubs/StateMachine.php.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\StateMachines';
    }
}
