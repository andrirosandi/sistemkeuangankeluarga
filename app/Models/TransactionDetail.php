<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $table = 'transaction_detail';
    protected $fillable = [
        'header_id',
        'description',
        'amount',
        'request_detail_id'
    ];

    public function header()
    {
        return $this->belongsTo(TransactionHeader::class, 'header_id');
    }

    public function requestDetail()
    {
        return $this->belongsTo(RequestDetail::class, 'request_detail_id');
    }
}
