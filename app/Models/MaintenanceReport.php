<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceReport extends Model
{
    protected $fillable = [
        'project_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'title',
        'description',
        'image_path',
        'status',
        'maintenance_date',
    ];

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
