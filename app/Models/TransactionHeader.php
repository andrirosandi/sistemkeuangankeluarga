<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasHeaderRelations;

class TransactionHeader extends Model
{
    use HasHeaderRelations;
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

    public function requestHeader()
    {
        return $this->belongsTo(RequestHeader::class, 'request_id');
    }
}
