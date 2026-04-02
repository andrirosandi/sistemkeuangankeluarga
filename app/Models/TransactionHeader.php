<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionHeader extends Model
{
    protected $table = 'transaction_header';
    protected $fillable = [
        'category_id',
        'description',
        'notes',
        'amount',
        'request_id',
        'trans_code',
        'transaction_date',
        'created_by',
        'status'
    ];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'header_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function requestHeader()
    {
        return $this->belongsTo(RequestHeader::class, 'request_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
