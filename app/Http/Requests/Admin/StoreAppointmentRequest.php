<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'donor_id' => 'required|exists:donors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'nullable|string|max:50',
            'units_to_donate' => 'nullable|integer|min:1|max:4',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
