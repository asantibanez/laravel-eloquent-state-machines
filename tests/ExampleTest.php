<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests;

use Orchestra\Testbench\TestCase;
use Asantibanez\LaravelEloquentStateMachines\LaravelEloquentStateMachinesServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelEloquentStateMachinesServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
