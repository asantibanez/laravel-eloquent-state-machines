<?php

namespace Asantibanez\LaravelEloquentStateMachines\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StateHistory
 * @package Asantibanez\LaravelEloquentStateMachines\Models
 * @property string $field
 * @property string $from
 * @property string $to
 * @property string $custom_properties
 * @property int $responsible_id
 * @property string $responsible_type
 * @property mixed $responsible
 * @property Carbon $created_at
 */
class StateHistory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'custom_properties' => 'array',
    ];

    public function getCustomProperty($key)
    {
        return data_get($this->custom_properties, $key, null);
    }

    public function responsible()
    {
        return $this->morphTo();
    }

    public function allCustomProperties()
    {
        return $this->custom_properties ?? [];
    }

    public function scopeForField($query, $field)
    {
        $query->where('field', $field);
    }

    public function scopeFrom($query, $from)
    {
        $query->where('from', $from);
    }

    public function scopeTo($query, $to)
    {
        $query->where('to', $to);
    }

    public function scopeWithCustomProperty($query, $key, $operator, $value = null)
    {
        $query->where("custom_properties->{$key}", $operator, $value);
    }
}
