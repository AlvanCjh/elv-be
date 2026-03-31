<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyNotification extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'message',
        'type',
        'sent_to_all',
        'sender_id',
        'recipient_id',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
