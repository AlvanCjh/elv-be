<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyAgenda extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'agenda_date',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
