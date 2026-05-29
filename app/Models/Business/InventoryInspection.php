<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InventoryInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_assignment_id',
        'user_id',
        'condition_status',
        'photo_url',
        'remarks',
    ];

    public function assignment()
    {
        return $this->belongsTo(InventoryAssignment::class, 'inventory_assignment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
