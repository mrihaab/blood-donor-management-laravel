<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BloodGroup;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_donor_cannot_view_another_donors_appointment_details_idor_prevention()
    {
        $group = BloodGroup::create(['name' => 'O+', 'description' => 'O Positive']);

        $donorUserA = User::factory()->create(['role' => 'donor']);
        $donorA = Donor::create([
            'user_id' => $donorUserA->id,
            'blood_group_id' => $group->id,
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'contact_number' => '1234567890',
            'address' => '123 Main St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip_code' => '10001',
        ]);

        $donorUserB = User::factory()->create(['role' => 'donor']);
        $donorB = Donor::create([
            'user_id' => $donorUserB->id,
            'blood_group_id' => $group->id,
            'gender' => 'female',
            'date_of_birth' => '1992-02-02',
            'contact_number' => '0987654321',
            'address' => '456 Side St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip_code' => '10002',
        ]);

        $appointmentB = Appointment::create([
            'donor_id' => $donorB->id,
            'appointment_date' => now()->addDays(5)->format('Y-m-d'),
            'appointment_time' => '10:00:00',
            'time_slot' => '10:00 AM',
            'units_to_donate' => 1,
            'status' => 'scheduled',
        ]);

        // Donor A attempts to view Donor B's appointment => HTTP 403 Forbidden
        $response = $this->actingAs($donorUserA)->get(route('donor.appointments.show', $appointmentB->id));

        $response->assertStatus(403);
    }

    public function test_donor_can_view_own_appointment_details()
    {
        $group = BloodGroup::create(['name' => 'A+', 'description' => 'A Positive']);

        $donorUser = User::factory()->create(['role' => 'donor']);
        $donor = Donor::create([
            'user_id' => $donorUser->id,
            'blood_group_id' => $group->id,
            'gender' => 'male',
            'date_of_birth' => '1985-05-05',
            'contact_number' => '5555555555',
            'address' => '789 Oak Ave',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip_code' => '10003',
        ]);

        $appointment = Appointment::create([
            'donor_id' => $donor->id,
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
            'appointment_time' => '14:00:00',
            'time_slot' => '02:00 PM',
            'units_to_donate' => 1,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($donorUser)->get(route('donor.appointments.show', $appointment->id));

        $response->assertStatus(200);
    }
}
