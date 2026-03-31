<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloorDrawingRevision extends Model
{
    protected $fillable = [
        'floor_id',
        'version_name',
        'revision_date',
        'remarks',
        'file_content',
        'file_path',
        'status',
        'created_by',
        'approved_by'
    ];


    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
