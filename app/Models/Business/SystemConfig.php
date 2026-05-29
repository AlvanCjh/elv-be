<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $fillable = ['key', 'value'];
}
