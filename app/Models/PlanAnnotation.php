<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanAnnotation extends Model
{
    protected $fillable = [
        'floor_id',
        'zone_id',
        'object_id',
        'system_type',
        'annotation_type',
        'geometry',
        'style',
        'created_by'
    ];

    protected $casts = [
        'geometry' => 'array',
        'style' => 'array',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function objectComponent()
    {
        return $this->belongsTo(ObjectComponent::class, 'object_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
