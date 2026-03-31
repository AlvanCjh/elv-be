<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyChecklist extends Model
{
    protected $fillable = [
        'project_id',
        'company_type',
        'check_date',
        'attendee_name',
        'verified_by',
        'status_summary',
        'sections_data',
        'remarks'
    ];

    protected $casts = [
        'sections_data' => 'array',
        'check_date' => 'date'
    ];

    public function project()
    {
        return $this->belongsTo(UserProject::class, 'project_id');
    }
}
