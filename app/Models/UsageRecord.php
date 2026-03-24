<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsageRecord extends Model
{
    protected $fillable = [
        'item_id',
        'item_type',
        'user_id',
        'date_out',
        'out_qty',
        'remarks',
        'expected_return_date',
        'return_date',
        'return_qty',
        'status'
    ];

    public function item()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}