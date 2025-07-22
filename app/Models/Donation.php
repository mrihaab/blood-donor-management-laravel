<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id',
        'blood_group_id',
        'blood_group',
        'quantity',
        'units',
        'donation_date',
        'status', // completed, dispensed, expired, etc.
        'notes',
        'collection_center',
        'created_by',
    ];

    protected $casts = [
        'donation_date' => 'date',
    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'donor_id', 'id');
    }
}
