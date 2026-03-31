<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoneObject extends Model
{
    protected $fillable = ['zone_id', 'name', 'description'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
