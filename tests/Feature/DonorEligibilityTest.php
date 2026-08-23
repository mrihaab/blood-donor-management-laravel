<?php

namespace Tests\Feature;

use App\Models\BloodGroup;
use App\Models\Donor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonorEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->bloodGroup = BloodGroup::firstOrCreate(
            ['name' => 'O+'],
            ['description' => 'O positive']
        );
    }

    public function test_donor_with_no_prior_donations_can_book_appointment()
    {
        $user = User::factory()->create(['role' => 'donor']);
        $donor = Donor::create([
            'user_id' => $user->id,
            'blood_group_id' => $this->bloodGroup->id,
            'gender' => 'male',
            'date_of_birth' => '1995-05-15',
            'contact_number' => '1234567890',
            'address' => '123 Main St',
            'city' => 'Metropolis',
            'state' => 'State',
            'zip_code' => '12345',
            'is_available' => true,
        ]);

        $response = $this->actingAs($user)->post(route('donor.appointments.store'), [
            'appointment_date' => now()->addDays(2)->format('Y-m-d'),
            'appointment_time' => '10:00',
            'units_to_donate' => 1,
            'notes' => 'First time donor',
        ]);

        $response->assertRedirect(route('donor.appointments.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('appointments', [
            'donor_id' => $donor->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_donor_with_recent_donation_10_days_ago_cannot_book()
    {
        $user = User::factory()->create(['role' => 'donor']);
        $donor = Donor::create([
            'user_id' => $user->id,
            'blood_group_id' => $this->bloodGroup->id,
            'gender' => 'male',
            'date_of_birth' => '1995-05-15',
            'contact_number' => '1234567890',
            'address' => '123 Main St',
            'city' => 'Metropolis',
            'state' => 'State',
            'zip_code' => '12345',
            'last_donation_date' => now()->subDays(10)->format('Y-m-d'),
            'is_available' => true,
        ]);

        $response = $this->actingAs($user)->post(route('donor.appointments.store'), [
            'appointment_date' => now()->addDays(2)->format('Y-m-d'),
            'appointment_time' => '10:00',
            'units_to_donate' => 1,
        ]);

        $response->assertSessionHasErrors(['appointment_date']);
        $this->assertDatabaseMissing('appointments', [
            'donor_id' => $donor->id,
        ]);
    }

    public function test_donor_with_donation_60_days_ago_can_book_again()
    {
        $user = User::factory()->create(['role' => 'donor']);
        $donor = Donor::create([
            'user_id' => $user->id,
            'blood_group_id' => $this->bloodGroup->id,
            'gender' => 'male',
            'date_of_birth' => '1995-05-15',
            'contact_number' => '1234567890',
            'address' => '123 Main St',
            'city' => 'Metropolis',
            'state' => 'State',
            'zip_code' => '12345',
            'last_donation_date' => now()->subDays(60)->format('Y-m-d'),
            'is_available' => true,
        ]);

        $response = $this->actingAs($user)->post(route('donor.appointments.store'), [
            'appointment_date' => now()->addDays(2)->format('Y-m-d'),
            'appointment_time' => '10:00',
            'units_to_donate' => 1,
        ]);

        $response->assertRedirect(route('donor.appointments.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('appointments', [
            'donor_id' => $donor->id,
            'status' => 'scheduled',
        ]);
    }
}
