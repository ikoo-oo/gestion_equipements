<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    use HasFactory;
    protected $fillable = [
        'employee_name',
        'department',
        'position',
        'equipment_description',
        'deadline',
        'status',
        'created_by',
        'assigned_to',
    ];

    /**
     * Deadline -treat it like a real date.
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'date',
        ];
    }

    /**
     * Relationship: Request belongs to a creator (HR user) id = 1
     * "Hey, the created_by column points to a user in the users table!"
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Request belongs to an assigned technician /Who accepted this mission?
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relationship:All messages/notifications about THIS quest
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'request_id');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'en_attente' => 'warning',
            'en_cours' => 'info',
            'termine' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'en_attente' => '🟡',
            'en_cours' => '🔵',
            'termine' => '✅',
            default => '⚪'
        };
    }
}
