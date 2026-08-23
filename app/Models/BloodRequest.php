<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hospital_id',
        'patient_id',
        'blood_group',
        'patient_name',
        'hospital',
        'city',
        'reason',
        'status',
        'donor_id',
        'assigned_donor_id',
        'units_needed',
        'urgency_level',
        'contact_number',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'assigned_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function hospitalEntity()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function getBloodGroupAttribute($value)
    {
        return $value;
    }

    public function bloodGroup()
    {
        return BloodGroup::where('name', $this->blood_group)->first();
    }

    public function scopeByBloodGroup($query, $bloodGroup)
    {
        return $query->where('blood_group', $bloodGroup);
    }
}
