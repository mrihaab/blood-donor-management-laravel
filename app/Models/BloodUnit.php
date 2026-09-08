<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_number',
        'din',
        'blood_group_id',
        'blood_group',
        'component_id',
        'component_type',
        'donor_id',
        'donation_id',
        'collection_date',
        'expiry_date',
        'volume_ml',
        'storage_location',
        'status',
        'temperature_status',
        'donation_type',
        'donor_relation',
        'replacement_patient_id',
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

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'blood_unit_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'blood_unit_id');
    }

    public function transfusionUnits()
    {
        return $this->hasMany(TransfusionUnit::class);
    }

    public function inspections()
    {
        return $this->hasMany(UnitInspection::class);
    }
}
