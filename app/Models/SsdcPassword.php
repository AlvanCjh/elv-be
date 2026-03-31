<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsdcPassword extends Model
{
    protected $fillable = ['project_id', 'system_name', 'username', 'password', 'notes'];

    public function project()
    {
        return $this->belongsTo(UserProject::class, 'project_id');
    }
}
