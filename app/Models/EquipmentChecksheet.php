<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentChecksheet extends Model
{
    protected $fillable = [
        'project_id', 'equipment_name', 'equipment_type', 'model_number', 
        'serial_number', 'location', 'voltage_v', 'current_a', 
        'other_readings', 'checklist_results', 'status', 'checked_by', 
        'check_date', 'remarks'
    ];

    protected $casts = [
        'checklist_results' => 'array',
        'check_date' => 'date'
    ];

    public function project()
    {
        return $this->belongsTo(UserProject::class, 'project_id');
    }
}
