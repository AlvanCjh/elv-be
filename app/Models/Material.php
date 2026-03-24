<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'material_name',
        'description',
        'brand',
        'unit_of_measure',
        'quantity_in_stock',
        'location',
        'project_id'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('project', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $projectId = request()->header('X-Project-Id');
            if ($projectId) {
                $builder->where('project_id', $projectId);
            }
        });
    }

    // Relationship to Usage Records as seen in your ERD
    public function usageRecords()
    {
        return $this->morphMany(UsageRecord::class , 'item');
    }

    public function stockInRecords()
    {
        return $this->morphMany(StockInRecord::class , 'item');
    }
}