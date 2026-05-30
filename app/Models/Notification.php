<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'request_id',
    ];

    /**
     * Relationship: Notification belongs to a user /Who Gets This Message?
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship:  What Quest Is This About? Notification is about a request
     */
    public function equipmentRequest()
    {
        return $this->belongsTo(EquipmentRequest::class, 'request_id');
    }

    /**
     * Get notification icon based on type
     */
    public function getIconAttribute()
    {
        return match($this->type) {
            'new_request' => '➕',
            'assigned_request' => '📋',
            'request_completed' => '✅',
            default => '🔔'
        };
    }

    /**
     * Get notification color based on type
     */
    public function getColorAttribute()
    {
        return match($this->type) {
            'new_request' => 'primary',
            'assigned_request' => 'info',
            'request_completed' => 'success',
            default => 'secondary'
        };
    }
}
