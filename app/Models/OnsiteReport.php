<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnsiteReport extends Model
{
    protected $fillable = [
        'project_id',
        'category',
        'title',
        'rfwi_ref_no',
        'location',
        'gridline_zone',
        'date_inspected',
        'consultant_comments',
        'description',
        'file_path',
        'status',
        'uploaded_by',
        'assigned_to',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function assigned_to_user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function project()
    {
        return $this->belongsTo(UserProject::class, 'project_id');
    }
}
