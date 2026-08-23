<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_request_id',
        'patient_id',
        'hospital_id',
        'administered_by',
        'started_at',
        'completed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function bloodRequest()
    {
        return $this->belongsTo(BloodRequest::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function administeredBy()
    {
        return $this->belongsTo(User::class, 'administered_by');
    }

    public function transfusionUnits()
    {
        return $this->hasMany(TransfusionUnit::class);
    }

    public function reactions()
    {
        return $this->hasMany(TransfusionReaction::class);
    }
}
