<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blood_group_id',
        'gender',
        'date_of_birth',
        'contact_number',
        'address',
        'city',
        'state',
        'zip_code',
        'last_donation_date',
        'health_info',
        'is_available',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_donation_date' => 'date',
        'is_available' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class);
    }
    
    public function donations()
{
    return $this->hasMany(Donation::class);
}

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Helper property to get blood group name
    public function getBloodGroupNameAttribute()
    {
        return $this->bloodGroup ? $this->bloodGroup->name : null;
    }

}
