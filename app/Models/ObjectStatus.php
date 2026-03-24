<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectStatus extends Model
{
    protected $fillable = [
        'object_id',
        'user_id',
        'current_status',
        'image_url',
        'remarks'
    ];

    public function objectComponent()
    {
        return $this->belongsTo(ObjectComponent::class, 'object_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
