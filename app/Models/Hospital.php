<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'license_number',
        'address',
        'city',
        'state',
        'contact_person',
        'contact_phone',
        'email',
        'status',
    ];

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function bloodRequests()
    {
        return $this->hasMany(BloodRequest::class);
    }
}
