<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'location',
        'units_to_donate',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime',
    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'donor_id', 'id');
    }
}
