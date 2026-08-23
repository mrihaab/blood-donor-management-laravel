<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_unit_id',
        'blood_group_id',
        'component_id',
        'transaction_type',
        'previous_quantity',
        'quantity_changed',
        'resulting_quantity',
        'reference_type',
        'reference_id',
        'user_id',
        'reason',
    ];

    public function bloodUnit()
    {
        return $this->belongsTo(BloodUnit::class);
    }

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function component()
    {
        return $this->belongsTo(BloodComponent::class, 'component_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
