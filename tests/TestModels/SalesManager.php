<?php

namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestModels;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableAlias;
use Illuminate\Database\Eloquent\Model;

class SalesManager extends Model implements AuthenticatableAlias
{
    use Authenticatable;

    protected $guarded = [];
}
