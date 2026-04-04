<?php

namespace App\Http\Controllers\Concerns;

trait TransactionType
{
    protected function getTransCode($type)
    {
        return $type === 'out' ? 2 : 1;
    }

    protected function getTypeLabel($type)
    {
        return $type === 'out' ? 'Kas Keluar' : 'Kas Masuk';
    }
}
