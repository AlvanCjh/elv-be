<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentationReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location_stored',
    ];
}
