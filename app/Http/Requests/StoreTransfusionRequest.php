<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransfusionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHospital());
    }

    public function rules(): array
    {
        return [
            'blood_request_id' => ['required', 'exists:blood_requests,id'],
            'patient_id'       => ['required', 'exists:patients,id'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
