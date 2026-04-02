<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $table = 'balance';
    protected $fillable = [
        'month',
        'begin',
        'total_in',
        'total_out',
        'ending'
    ];
}
