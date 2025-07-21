<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodRequestAssignment extends Model
{
    protected $fillable = [
        'blood_request_id',
        'donor_id',
        'status',
        'notes',
        'notified_at',
        'responded_at'
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeNotified($query)
    {
        return $query->where('status', 'notified');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function markAsNotified()
    {
        $this->update([
            'status' => 'notified',
            'notified_at' => now()
        ]);
    }

    public function markAsAccepted()
    {
        $this->update([
            'status' => 'accepted',
            'responded_at' => now()
        ]);
    }

    public function markAsDeclined()
    {
        $this->update([
            'status' => 'declined',
            'responded_at' => now()
        ]);
    }
}
