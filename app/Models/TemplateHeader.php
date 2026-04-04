<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasHeaderRelations;

class TemplateHeader extends Model
{
    use HasHeaderRelations;
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

}
