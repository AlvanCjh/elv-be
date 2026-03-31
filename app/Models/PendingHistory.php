<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingHistory extends Model
{
    protected $fillable = [
        'floor_annotation_id',
        'name',
        'date',
        'base_location',
        'status',
        'remarks',
        'updated_by_user_id',
    ];

    public function annotation()
    {
        return $this->belongsTo(FloorAnnotation::class, 'floor_annotation_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by_user_id');
    }
}
