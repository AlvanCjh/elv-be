<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyDocument extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'file_path',
        'uploaded_by',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
