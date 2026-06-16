<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientsPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'collected_by',
        'due_date',
        'client_id',
        'client_loans_id',
        'client_area',
        'daily',
        'old_balance',
        'collection',
        'type',
        'is_lapsed',
        'is_collected',
        'created_by'
    ];
}