<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProject extends Model
{
    protected $table = 'projects';
    protected $fillable = ['user_id', 'building_id', 'name', 'location', 'started_at', 'latitude', 'longitude', 'is_facilitator_only'];

    protected $casts = [
        'started_at' => 'datetime',
        'is_facilitator_only' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
