<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorDeferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id',
        'deferral_type',
        'reason',
        'start_date',
        'end_date',
        'created_by',
        'notes',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCurrentlyActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->deferral_type === 'permanent') {
            return true;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }
}
