<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoneComplaint extends Model
{
    protected $fillable = ['zone_id', 'user_id', 'category', 'message', 'status'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
