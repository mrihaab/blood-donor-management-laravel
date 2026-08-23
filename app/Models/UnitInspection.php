<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_unit_id',
        'inspector_id',
        'cold_chain_intact',
        'seal_intact',
        'elapsed_time_minutes',
        'visual_inspection_passed',
        'decision',
        'notes',
        'inspected_at',
    ];

    protected $casts = [
        'cold_chain_intact' => 'boolean',
        'seal_intact' => 'boolean',
        'visual_inspection_passed' => 'boolean',
        'inspected_at' => 'datetime',
    ];

    public function bloodUnit()
    {
        return $this->belongsTo(BloodUnit::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
