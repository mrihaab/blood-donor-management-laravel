<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodInventory extends Model
{
    use HasFactory;

    protected $table = 'blood_inventory';

    protected $fillable = [
        'blood_group_id',
        'quantity',
        'units_available',
        'units_requested',
        'expiry_date',
        'location',
        'status',
        'donor_id',
        'collection_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'collection_date' => 'date',
    ];

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                    ->where('expiry_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now());
    }
}
