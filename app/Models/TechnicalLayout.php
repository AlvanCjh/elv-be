<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicalLayout extends Model
{
    protected $fillable = ['project_id', 'name', 'image_path'];

    public function zones()
    {
        return $this->hasMany(TechnicalLayoutZone::class);
    }

    public function project()
    {
        return $this->belongsTo(UserProject::class, 'project_id');
    }
}
