<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateDetail extends Model
{
    protected $table = 'template_detail';
    protected $fillable = [
        'header_id',
        'description',
        'amount'
    ];

    public function header()
    {
        return $this->belongsTo(TemplateHeader::class, 'header_id');
    }
}
