<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectComponent extends Model
{
    protected $fillable = [
        'zone_id',
        'user_id',
        'item_alias_id',
        'item_name',
        'gridline_coords',
        'system_type',
        'cabling_type',
        'conduit_length_m',
        'trunking_length_m',
        'cable_length_m',
        'pos_x',
        'pos_y',
        'rotation',
        'shape_type',
        'geometry',
        'project_id'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('project', new class implements \Illuminate\Database\Eloquent\Scope {
            public function apply(\Illuminate\Database\Eloquent\Builder $builder, \Illuminate\Database\Eloquent\Model $model): void
            {
                $projectId = request()->header('X-Project-Id');
                if ($projectId) {
                    $builder->where($model->getTable() . '.project_id', $projectId);
                }
            }
        });
    }

    protected $casts = [
        'geometry' => 'array',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statuses()
    {
        return $this->hasMany(ObjectStatus::class , 'object_id');
    }

    public function latestStatus()
    {
        return $this->hasOne(ObjectStatus::class , 'object_id')->latestOfMany();
    }

    public function ports()
    {
        return $this->hasMany(ObjectPort::class , 'object_component_id');
    }

    public function finishStatus()
    {
        return $this->hasOne(ObjectStatus::class, 'object_id')->where('current_status', 'Finish')->latest();
    }

    public function approveStatus()
    {
        return $this->hasOne(ObjectStatus::class, 'object_id')->where('current_status', 'Approved')->latest();
    }
}