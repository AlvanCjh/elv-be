<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legend extends Model
{
    protected $fillable = [
        'system_type',
        'name',
        'shape_type',
        'unit',
        'unit_cost',
        'icon_svg',
        'style',
    ];

    protected $casts = [
        'style' => 'array',
    ];

    public function floors()
    {
        return $this->belongsToMany(Floor::class , 'floor_legend')->withTimestamps();
    }
}