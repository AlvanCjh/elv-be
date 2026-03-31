<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = ['project_id', 'user_id', 'user_name', 'date', 'shift'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
