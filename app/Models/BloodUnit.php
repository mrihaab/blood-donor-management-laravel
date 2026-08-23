<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_number',
        'blood_group_id',
        'component_id',
        'donor_id',
        'donation_id',
        'collection_date',
        'expiry_date',
        'volume_ml',
        'storage_location',
        'status',
    ];

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function component()
    {
        return $this->belongsTo(BloodComponent::class, 'component_id');
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
