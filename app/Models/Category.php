<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $fillable = ['name'];

    public function requestHeaders()
    {
        return $this->hasMany(RequestHeader::class);
    }

    public function transactionHeaders()
    {
        return $this->hasMany(TransactionHeader::class);
    }
}
