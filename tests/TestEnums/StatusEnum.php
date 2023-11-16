<?php


namespace Asantibanez\LaravelEloquentStateMachines\Tests\TestEnums;

enum StatusEnum: string
{
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';
    case PENDING = 'pending';
    case PROCESSED = 'processed';
    case WAITING = 'waiting';
}
