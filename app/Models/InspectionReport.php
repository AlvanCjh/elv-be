<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionReport extends Model
{
    protected $fillable = [
        'project_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'title',
        'rfwi_ref_no',
        'location',
        'gridline_zone',
        'date_inspected',
        'consultant_comments',
        'description',
        'file_path',
        'status',
        'inspection_date',
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
