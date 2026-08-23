<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransfusionReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfusion_id',
        'blood_unit_id',
        'reaction_type',
        'severity',
        'symptoms',
        'onset_at',
        'reported_at',
        'reported_by',
        'action_taken',
        'outcome',
        'notes',
    ];

    protected $casts = [
        'onset_at' => 'datetime',
        'reported_at' => 'datetime',
    ];

    public function transfusion()
    {
        return $this->belongsTo(Transfusion::class);
    }

    public function bloodUnit()
    {
        return $this->belongsTo(BloodUnit::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
