<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Collector extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'collectors';

    protected $fillable = [
        'fullname',
        'email',
        'password',
        'phone',
        'gender',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
