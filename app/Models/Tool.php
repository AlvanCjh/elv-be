<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    protected $fillable = [
        'tool_name',
        'description',
        'brand',
        'quantity_in_stock',
        'location',
        'status',
        'type',
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

    public function usageRecords()
    {
        return $this->morphMany(UsageRecord::class , 'item');
    }

    public function stockInRecords()
    {
        return $this->morphMany(StockInRecord::class , 'item');
    }
}