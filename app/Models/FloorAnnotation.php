<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloorAnnotation extends Model
{
    protected $fillable = [
        'project_id',
        'floor_id', 'zone_id', 'category', 'riser_id',
        'legend_id',                            // FK to legends table (replaces annotation_type enum)
        'name', 'description',
        'coordinates',                          // JSON array of {x,y} objects (% of image size)
        'rotation',                             // degrees (0-359), for point types only
        'status', 'remarks', 'not_our_fault', 'photo_path',
        'due_date',                             // nullable date: expected fix/resolution date
    ];

    protected $casts = [
        'coordinates'  => 'array',
        'rotation'     => 'integer',
        'not_our_fault' => 'boolean',
        'due_date'     => 'date:Y-m-d',
    ];

    public function floor()  { return $this->belongsTo(Floor::class); }
    public function zone()   { return $this->belongsTo(Zone::class); }
    public function legend() { return $this->belongsTo(\App\Models\Legend::class); }

    public function cablePorts()
    {
        return $this->hasMany(CablePort::class, 'annotation_id');
    }
}
