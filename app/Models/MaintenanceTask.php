<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceTask extends Model
{
    protected $fillable = ['project_id', 'type', 'title', 'description', 'assigned_to', 'date', 'status'];

    public function project()
    {
        return $this->belongsTo(UserProject::class, 'project_id');
    }
}
