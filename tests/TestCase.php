<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests;

use CreatePendingTransitionsTable;
use CreateSalesOrdersTable;
use CreateStateHistoriesTable;
use Javoscript\MacroableModels\MacroableModelsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Asantibanez\LaravelEloquentStateMachines\LaravelEloquentStateMachinesServiceProvider;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/../database/factories');

        $this->withFactories(__DIR__.'/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            MacroableModelsServiceProvider::class,
            LaravelEloquentStateMachinesServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        include_once __DIR__ . '/../database/migrations/create_state_histories_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_pending_transitions_table.php.stub';

        include_once __DIR__ . '/database/migrations/create_sales_orders_table.php';

        (new CreateStateHistoriesTable())->up();
        (new CreatePendingTransitionsTable())->up();
        (new CreateSalesOrdersTable())->up();
    }
}
