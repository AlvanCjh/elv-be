<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectPort extends Model
{
    protected $fillable = [
        'object_component_id',
        'port_name',
        'cable_id',
        'connected_to_object_id',
        'connected_port_name',
        'status',
    ];

    public function objectComponent()
    {
        return $this->belongsTo(ObjectComponent::class);
    }

    public function connectedToObject()
    {
        return $this->belongsTo(ObjectComponent::class , 'connected_to_object_id');
    }
}