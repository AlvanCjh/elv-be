<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineHistory extends Model
{
    protected $fillable = [
        'project_id',
        'timeline_task_id',
        'user_id',
        'entity_name',
        'change_details',
        'reason',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(TimelineTask::class, 'timeline_task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
