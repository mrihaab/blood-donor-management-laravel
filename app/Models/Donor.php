<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function deferrals()
    {
        return $this->hasMany(DonorDeferral::class);
    }

    public function screenings()
    {
        return $this->hasMany(DonorScreening::class);
    }

    // Helper property to get blood group name
    public function getBloodGroupNameAttribute()
    {
        return $this->bloodGroup ? $this->bloodGroup->name : null;
    }

    /**
     * Get the latest completed donation date.
     */
    public function getLastDonationDate()
    {
        if ($this->last_donation_date) {
            return Carbon::parse($this->last_donation_date);
        }

        $latestDonation = $this->donations()->where('status', 'completed')->latest('donation_date')->first();

        return $latestDonation ? Carbon::parse($latestDonation->donation_date) : null;
    }

    /**
     * Check if donor is eligible to donate (56 days interval).
     */
    public function isEligibleToDonate(): bool
    {
        $lastDonationDate = $this->getLastDonationDate();

        if (!$lastDonationDate) {
            return true;
        }

        return $lastDonationDate->diffInDays(now()) >= 56;
    }

    /**
     * Get the next eligible donation date.
     */
    public function getNextEligibleDate()
    {
        $lastDonationDate = $this->getLastDonationDate();

        if (!$lastDonationDate) {
            return now();
        }

        return $lastDonationDate->copy()->addDays(56);
    }

    /**
     * Get remaining days until eligible to donate.
     */
    public function getDaysUntilEligible(): int
    {
        if ($this->isEligibleToDonate()) {
            return 0;
        }

        return (int) ceil(now()->diffInDays($this->getNextEligibleDate(), false));
    }
}
