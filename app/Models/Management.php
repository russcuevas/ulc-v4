<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Management extends Authenticatable
{
    use HasFactory;

    protected $table = 'managements';

    protected $fillable = [
        'fullname',
        'email',
        'password',
        'phone',
        'gender',
    ];

    protected $hidden = [
        'password',
    ];
}
