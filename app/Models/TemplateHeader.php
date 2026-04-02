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
        'amount',
        'created_by'
    ];

    public function details()
    {
        return $this->hasMany(TemplateDetail::class, 'header_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
