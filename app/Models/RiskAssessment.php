<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
    protected $fillable = [
        'project_id',
        'created_by_user_id',
        'title',
        'type',
        'risk_level',
        'description',
        'mitigation_plan',
        'status',
        'assessment_date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
