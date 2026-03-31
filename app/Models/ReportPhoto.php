<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportPhoto extends Model
{
    protected $fillable = ['reportable_id', 'reportable_type', 'photo_path', 'caption'];

    public function reportable()
    {
        return $this->morphTo();
    }
}
