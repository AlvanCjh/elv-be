<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $fillable = ['project_id', 'name', 'total_floor', 'latitude', 'longitude'];

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

        // Automatically set project_id on creation if missing
        static::creating(function ($model) {
            if (!$model->project_id && app()->has('active_project_id')) {
                $model->project_id = app('active_project_id');
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }
}