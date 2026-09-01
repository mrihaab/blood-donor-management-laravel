<?php

namespace App\Mail;

use App\Models\BloodRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmergencyBloodRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public BloodRequest $bloodRequest;
    public User $donorUser;

    public function __construct(BloodRequest $bloodRequest, User $donorUser)
    {
        $this->bloodRequest = $bloodRequest;
        $this->donorUser = $donorUser;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🚨 URGENT: Emergency {$this->bloodRequest->blood_group} Blood Needed at {$this->bloodRequest->hospital}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.emergency-request',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
