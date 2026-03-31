<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqItem extends Model
{
    protected $fillable = [
        'project_id',
        'floor_id',
        'category',
        'item_code',
        'item_name',
        'total_quantity'
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }
}
