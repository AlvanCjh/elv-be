<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CablePort extends Model
{
    protected $fillable = [
        'annotation_id',
        'port_number',
        'status',
        'notes'
    ];

    /**
     * The device that owns this port.
     */
    public function annotation(): BelongsTo
    {
        return $this->belongsTo(FloorAnnotation::class, 'annotation_id');
    }

    /**
     * Get the connection where this port is the source (upstream).
     */
    public function connectionAsSource(): HasOne
    {
        return $this->hasOne(CableConnection::class, 'port_b_id');
    }

    /**
     * Get the connection where this port is the sink (downstream).
     */
    public function connectionAsSink(): HasOne
    {
        return $this->hasOne(CableConnection::class, 'port_a_id');
    }
}
