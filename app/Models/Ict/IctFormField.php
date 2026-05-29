<?php

namespace App\Models\Ict;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IctFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'field_key',
        'label',
        'type',
        'required',
        'options',
        'section',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];
}
