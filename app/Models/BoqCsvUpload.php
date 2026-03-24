<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;

class BoqCsvUpload extends Model
{
    protected $fillable = ['project_id', 'user_id', 'filename'];

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

    public function items()
    {
        return $this->hasMany(BoqCsvItem::class);
    }
}