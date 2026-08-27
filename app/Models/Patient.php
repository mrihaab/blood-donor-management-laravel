<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'name',
        'mrn',
        'gender',
        'date_of_birth',
        'blood_group_id',
        'contact_number',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function bloodRequests()
    {
        return $this->hasMany(BloodRequest::class);
    }
}
