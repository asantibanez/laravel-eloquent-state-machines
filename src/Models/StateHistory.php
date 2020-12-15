<?php

namespace Asantibanez\LaravelEloquentStateMachines\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

/**
 * Class StateHistory
 * @package Asantibanez\LaravelEloquentStateMachines\Models
 * @property string $transition
 * @property string $from
 * @property string $to
 * @property string $custom_properties
 * @property int $responsible_id
 * @property string $responsible_type
 * @property mixed $responsible
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
