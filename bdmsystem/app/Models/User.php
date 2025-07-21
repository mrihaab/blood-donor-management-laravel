<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin()
{
    return $this->role === 'admin';
}

    // ✅ Relationship: A user has one donor profile (only if role is 'donor')
  public function donor()
{
    return $this->hasOne(Donor::class);
}


    // ✅ Optional helper if you want to quickly get blood group from user
   public function bloodGroup()
{
    return $this->belongsTo(BloodGroup::class, 'blood_group_id');
}


    // ✅ Optional helper if you want to access donations via user
  public function donations()
{
    return $this->hasMany(\App\Models\Donation::class, 'donor_id');
}

}
