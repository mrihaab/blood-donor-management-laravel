<?php

namespace App\Http\Requests\Donor;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'donor';
    }

    public function rules(): array
    {
        return [
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
