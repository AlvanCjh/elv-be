<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CableConnection extends Model
{
    protected $fillable = [
        'cable_id',
        'cable_type',
        'port_a_id',
        'port_b_id'
    ];

    /**
     * The downstream (sink) end of the cable.
     */
    public function portA(): BelongsTo
    {
        return $this->belongsTo(CablePort::class, 'port_a_id');
    }

    /**
     * The upstream (source) end of the cable.
     */
    public function portB(): BelongsTo
    {
        return $this->belongsTo(CablePort::class, 'port_b_id');
    }
}
