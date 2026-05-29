<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimelineTask extends Model
{
    /** @use HasFactory<\Database\Factories\TimelineTaskFactory> */
    use HasFactory;
    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
        'expected_start_date',
        'expected_end_date',
        'actual_start_date',
        'actual_end_date',
        'edit_reason',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function parent()
    {
        return $this->belongsTo(TimelineTask::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TimelineTask::class, 'parent_id');
    }
}
