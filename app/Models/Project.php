<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'description', 'type', 'start_date', 'end_date', 'actual_start_date', 'actual_end_date', 'edit_reason'];
    public function buildings()
    {
        return $this->hasMany(Building::class);
    }

    public function boqCsvUploads()
    {
        return $this->hasMany(BoqCsvUpload::class);
    }
}