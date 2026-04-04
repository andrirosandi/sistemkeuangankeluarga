<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Traits\HasHeaderRelations;

class RequestHeader extends Model implements HasMedia
{
    use InteractsWithMedia, HasHeaderRelations;

    protected $table = 'request_header';
    protected $fillable = [
        'category_id',
        'request_date',
        'trans_code',
        'priority',
        'description',
        'notes',
        'amount',
        'status',
        'rejection_reason',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    public function details()
    {
        return $this->hasMany(RequestDetail::class, 'header_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transaction()
    {
        return $this->hasOne(TransactionHeader::class, 'request_id');
    }
}
