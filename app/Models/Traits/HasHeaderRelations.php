<?php

namespace App\Models\Traits;

use App\Models\Category;
use App\Models\User;

trait HasHeaderRelations
{
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
