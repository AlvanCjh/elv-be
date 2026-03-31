<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalLayoutZone extends Model
{
    use HasFactory;

    protected $fillable = ['technical_layout_id', 'name', 'svg_path', 'color', 'status'];

    public function layout()
    {
        return $this->belongsTo(TechnicalLayout::class, 'technical_layout_id');
    }

    public function objects()
    {
        return $this->hasMany(TechnicalLayoutZoneObject::class);
    }

    public function annotations()
    {
        return $this->hasMany(TechnicalLayoutZoneAnnotation::class);
    }
}
