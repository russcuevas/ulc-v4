<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Areas extends Model
{
    use HasFactory;

    protected $fillable = [
        'secretary_id',
        'collector_id',
        'location_name',
        'areas_name',
    ];
}
