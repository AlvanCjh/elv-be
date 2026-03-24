<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockInRecord extends Model
{
    protected $fillable = ['item_id', 'item_type', 'date_in', 'in_qty', 'remarks'];

    public function item()
    {
        return $this->morphTo();
    }
}
