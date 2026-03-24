<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqCsvItem extends Model
{
    protected $fillable = [
        'boq_csv_upload_id',
        'item_id',
        'alias_prefix',
        'legend_dbn_name',
        'floor_number',
        'status',
    ];

    public function upload()
    {
        return $this->belongsTo(BoqCsvUpload::class , 'boq_csv_upload_id');
    }
}