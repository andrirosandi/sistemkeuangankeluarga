<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestDetail extends Model
{
    protected $table = 'request_detail';
    protected $fillable = [
        'header_id',
        'description',
        'amount',
        'status'
    ];

    public function header()
    {
        return $this->belongsTo(RequestHeader::class, 'header_id');
    }
}
