<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CertifyReturnedUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHospital());
    }

    public function rules(): array
    {
        return [
            'cold_chain_intact'        => ['required', 'boolean'],
            'seal_intact'              => ['required', 'boolean'],
            'elapsed_time_minutes'     => ['required', 'integer', 'min:0'],
            'visual_inspection_passed' => ['required', 'boolean'],
            'notes'                    => ['nullable', 'string'],
        ];
    }
}
