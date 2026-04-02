<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    
    // UUID or string PK? Default ID is string key usually for settings ?
    // Let's assume standard ID for now or string primary key?
    // Let's check migration. If key is primary.
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];
}
