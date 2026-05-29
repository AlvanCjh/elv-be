<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InventoryStockIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_inventory_id',
        'user_id',
        'quantity',
    ];

    public function inventory()
    {
        return $this->belongsTo(BusinessInventory::class, 'business_inventory_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
