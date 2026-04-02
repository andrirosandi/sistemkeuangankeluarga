<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestHeader extends Model
{
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
