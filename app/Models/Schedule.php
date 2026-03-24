<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'assigned_to_user_id',
        'assigned_team',
        'project_id',
        'created_by_user_id',
        'status',
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
