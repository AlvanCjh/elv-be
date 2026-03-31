<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Riser extends Model
{
    protected $fillable = ['project_id', 'floor_id', 'name', 'location', 'annotation_id'];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function racks(): HasMany
    {
        return $this->hasMany(FloorAnnotation::class, 'riser_id');
    }
}
