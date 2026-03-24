<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['floor_id', 'alias_id', 'name', 'area', 'location_desc', 'svg_path'];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function objectComponents()
    {
        return $this->hasMany(ObjectComponent::class);
    }
}
