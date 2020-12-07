<?php

namespace Asantibanez\LaravelEloquentStateMachines;

use Asantibanez\LaravelEloquentStateMachines\Commands\MakeStateMachine;
use Illuminate\Support\ServiceProvider;

class LaravelEloquentStateMachinesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-eloquent-state-machines.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_state_histories_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_state_histories_table.php'),
            ], 'migrations');

            $this->commands([
                MakeStateMachine::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-eloquent-state-machines');
    }
}
