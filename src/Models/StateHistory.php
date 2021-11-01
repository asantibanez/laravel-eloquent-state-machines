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
 * @property array $custom_properties
 * @property int $responsible_id
 * @property string $responsible_type
 * @property mixed $responsible
 * @property Carbon $created_at
 * @property array $changed_attributes
 */
class StateHistory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'custom_properties' => 'array',
        'changed_attributes' => 'array',
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

    public function changedAttributesNames()
    {
        return collect($this->changed_attributes ?? [])->keys()->toArray();
    }

    public function changedAttributeOldValue($attribute)
    {
        return data_get($this->changed_attributes, "$attribute.old", null);
    }

    public function changedAttributeNewValue($attribute)
    {
        return data_get($this->changed_attributes, "$attribute.new", null);
    }

    public function scopeForField($query, $field)
    {
        $query->where('field', $field);
    }

    public function scopeFrom($query, $from)
    {
        if (is_array($from)) {
            $query->whereIn('from', $from);
        } else {
            $query->where('from', $from);
        }
    }

    public function scopeTransitionedFrom($query, $from)
    {
        $query->from($from);
    }

    public function scopeTo($query, $to)
    {
        if (is_array($to)) {
            $query->whereIn('to', $to);
        } else {
            $query->where('to', $to);
        }
    }

    public function scopeTransitionedTo($query, $to)
    {
        $query->to($to);
    }

    public function scopeWithTransition($query, $from, $to)
    {
        $query->from($from)->to($to);
    }

    public function scopeWithCustomProperty($query, $key, $operator, $value = null)
    {
        $query->where("custom_properties->{$key}", $operator, $value);
    }

    public function scopeWithResponsible($query, $responsible)
    {
        if ($responsible instanceof Model) {
            return $query
                ->where('responsible_id', $responsible->getKey())
                ->where('responsible_type', get_class($responsible))
            ;
        }

        return $query->where('responsible_id', $responsible);
    }
}
