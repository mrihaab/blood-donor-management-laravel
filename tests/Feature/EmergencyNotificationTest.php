<?php

namespace Tests\Feature;

use App\Models\BloodGroup;
use App\Models\Donor;
use App\Models\User;
use App\Notifications\EmergencyBloodRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmergencyNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_emergency_blood_request_creation_dispatches_notification_to_matching_donors()
    {
        Notification::fake();

        $bloodGroup = BloodGroup::firstOrCreate(
            ['name' => 'B+'],
            ['description' => 'B positive']
        );

        $requestingUser = User::factory()->create(['role' => 'donor']);
        
        $matchingDonorUser = User::factory()->create(['role' => 'donor', 'status' => 'active']);
        Donor::create([
            'user_id' => $matchingDonorUser->id,
            'blood_group_id' => $bloodGroup->id,
            'gender' => 'female',
            'date_of_birth' => '1998-08-08',
            'contact_number' => '9876543210',
            'address' => '456 City Rd',
            'city' => 'Metropolis',
            'state' => 'State',
            'zip_code' => '54321',
            'is_available' => true,
        ]);

        $response = $this->actingAs($requestingUser)->post(route('donor.blood_requests.store'), [
            'blood_group' => 'B+',
            'patient_name' => 'John Doe',
            'hospital' => 'City General Hospital',
            'city' => 'Metropolis',
            'units_needed' => 2,
            'reason' => 'Emergency surgery',
        ]);

        $response->assertRedirect(route('donor.blood_requests.index'));
        
        Notification::assertSentTo(
            $matchingDonorUser,
            EmergencyBloodRequestNotification::class,
            function ($notification) {
                return $notification->bloodRequest->patient_name === 'John Doe' &&
                       $notification->bloodRequest->blood_group === 'B+';
            }
        );
    }
}
