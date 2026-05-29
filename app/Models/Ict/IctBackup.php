<?php

namespace App\Models\Ict;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IctBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'backup_date',
        'responsible_first_name',
        'responsible_last_name',
        'department',
        'systems',
        'status',
        'location',
        'attachment_url',
        'project_id',
        'custom_fields',
    ];

    protected $casts = [
        'systems' => 'array',
        'custom_fields' => 'array',
        'backup_date' => 'date'
    ];
}
