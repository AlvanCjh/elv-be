<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    protected $fillable = ['building_id', 'floor_number', 'type', 'floor_plan_svg'];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function legends()
    {
        return $this->belongsToMany(Legend::class, 'floor_legend')->withTimestamps();
    }
}
