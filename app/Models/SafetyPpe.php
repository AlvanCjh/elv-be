<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyPpe extends Model
{
    protected $fillable = [
        'project_id',
        'item_name',
        'status',
        'assigned_to',
        'remarks',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
