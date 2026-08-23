<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'storage_temperature',
        'shelf_life_days',
        'status',
    ];

    public function bloodUnits()
    {
        return $this->hasMany(BloodUnit::class, 'component_id');
    }
}
