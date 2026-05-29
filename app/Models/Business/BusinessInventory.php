<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_tag',
        'category',
        'brand',
        'model',
        'serial_number',
        'status',
        'quantity',
        'image_url',
        'purchase_date',
        'cost',
        'remarks',
    ];
}
