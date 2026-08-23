<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorScreening extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'donor_id',
        'screened_by',
        'blood_pressure',
        'pulse',
        'temperature',
        'weight',
        'hemoglobin',
        'status',
        'notes',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function screener()
    {
        return $this->belongsTo(User::class, 'screened_by');
    }
}
