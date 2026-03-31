<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReport extends Model
{
    protected $fillable = [
        'project_id', 'service_report_no', 'company_name', 'address',
        'contact_person', 'telephone_no', 'taken_by', 'date_time',
        'service_types', 'service_type_others_text', 'description',
        'service_summary', 'summary_date', 'summary_time'
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'summary_date' => 'date',
        'service_types' => 'array',
        'service_summary' => 'array', // Use JSON for list of steps
    ];

    public function project()
    {
        return $this->belongsTo(UserProject::class, 'project_id');
    }

    public function photos()
    {
        return $this->morphMany(ReportPhoto::class, 'reportable');
    }
}
