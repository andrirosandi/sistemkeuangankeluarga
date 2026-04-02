<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateHeader extends Model
{
    protected $table = 'template_header';
    protected $fillable = [
        'category_id',
        'trans_code',
        'description',
        'amount'
    ];

    public function details()
    {
        return $this->hasMany(TemplateDetail::class, 'header_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
