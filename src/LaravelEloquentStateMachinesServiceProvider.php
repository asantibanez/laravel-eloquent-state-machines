<?php

namespace Asantibanez\LaravelEloquentStateMachines;

use Illuminate\Support\ServiceProvider;

class LaravelEloquentStateMachinesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-eloquent-state-machines');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-eloquent-state-machines');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-eloquent-state-machines.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-eloquent-state-machines'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-eloquent-state-machines'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-eloquent-state-machines'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-eloquent-state-machines');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-eloquent-state-machines', function () {
            return new LaravelEloquentStateMachines;
        });
    }
}
