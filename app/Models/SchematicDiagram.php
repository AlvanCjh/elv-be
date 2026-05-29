<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchematicDiagram extends Model
{
    protected $fillable = ['project_id', 'uploaded_by_user_id', 'project_title', 'file_path', 'status'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
