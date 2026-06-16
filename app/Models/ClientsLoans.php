<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientsLoans extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'pn_number',
        'release_number',
        'loan_from',
        'loan_to',
        'loan_amount',
        'balance',
        'daily',
        'principal',
        'loan_status',
        'loan_terms',
        'status',
        'created_by',
    ];
}