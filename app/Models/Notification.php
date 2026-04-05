<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'user_id',
        'message',
        'route_name',
        'route_params',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'route_params' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRedirectUrl()
    {
        return route('notification.read', $this->id);
    }

    public function getDestinationUrl()
    {
        if ($this->route_name) {
            return route($this->route_name, $this->route_params ?? []);
        }
        
        return route('notification.index');
    }
}
