<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransfusionUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfusion_id',
        'blood_unit_id',
        'issued_at',
        'started_at',
        'completed_at',
        'returned_at',
        'disposition',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function transfusion()
    {
        return $this->belongsTo(Transfusion::class);
    }

    public function bloodUnit()
    {
        return $this->belongsTo(BloodUnit::class);
    }
}
