<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalLayoutZoneAnnotation extends Model
{
    use HasFactory;

    protected $fillable = ['technical_layout_zone_id', 'title', 'description', 'status', 'priority', 'photo_path'];

    public function zone()
    {
        return $this->belongsTo(TechnicalLayoutZone::class, 'technical_layout_zone_id');
    }
}
