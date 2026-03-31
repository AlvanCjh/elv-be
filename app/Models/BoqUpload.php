<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqUpload extends Model
{
    protected $fillable = [
        'project_id',
        'file_name',
        'file_path',
        'total_inserted_rows',
    ];

    public function project()
    {
        return $this->belongsTo(UserProject::class, 'project_id');
    }
}
