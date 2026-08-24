<?php

namespace App\Notifications;

use App\Models\BloodRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmergencyBloodRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public $bloodRequest;

    public function __construct(BloodRequest $bloodRequest)
    {
        $this->bloodRequest = $bloodRequest;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('URGENT: Emergency Blood Request for Blood Group ' . $this->bloodRequest->blood_group)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('An urgent blood request has been created that matches your blood group.')
            ->line('Patient: ' . $this->bloodRequest->patient_name)
            ->line('Blood Group Needed: ' . $this->bloodRequest->blood_group)
            ->line('Hospital: ' . $this->bloodRequest->hospital)
            ->line('City: ' . $this->bloodRequest->city)
            ->line('Units Needed: ' . $this->bloodRequest->units_needed)
            ->action('View Blood Requests', url('/donor/blood-requests'))
            ->line('Thank you for being a lifesaving blood donor!');
    }

    public function toArray($notifiable): array
    {
        return [
            'blood_request_id' => $this->bloodRequest->id,
            'blood_group' => $this->bloodRequest->blood_group,
            'hospital' => $this->bloodRequest->hospital,
            'city' => $this->bloodRequest->city,
            'units_needed' => $this->bloodRequest->units_needed,
            'message' => 'Emergency blood request created for blood group ' . $this->bloodRequest->blood_group,
        ];
    }
}
