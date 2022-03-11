<?php

namespace Asantibanez\LaravelEloquentStateMachines\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PendingTransition
 * @package Asantibanez\LaravelEloquentStateMachines\Models
 * @property string $field
 * @property string $from
 * @property string $to
 * @property Carbon $transition_at
 * @property Carbon $applied_at
 * @property string $custom_properties
 * @property int $model_id
 * @property string $model_type
 * @property Model $model
 * @property int $responsible_id
 * @property string $responsible_type
 * @property Model $responsible
 * @property string $hook_data
 */
class PendingTransition extends Model
{
    protected $guarded = [];

    protected $casts = [
        'custom_properties' => 'array',
        'hook_data' => 'array',
    ];

    protected $dates = [
        'transition_at' => 'date',
        'applied_at' => 'date',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function responsible()
    {
        return $this->morphTo();
    }

    public function scopeNotApplied($query)
    {
        $query->whereNull('applied_at');
    }

    public function scopeOnScheduleOrOverdue($query)
    {
        $query->where('transition_at', '<=', now());
    }

    public function scopeForField($query, $field)
    {
        $query->where('field', $field);
    }
}
