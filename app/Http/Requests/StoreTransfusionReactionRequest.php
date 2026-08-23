<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransfusionReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHospital());
    }

    public function rules(): array
    {
        return [
            'reaction_type' => ['required', 'string', 'max:255'],
            'severity'      => ['required', 'string', 'in:mild,moderate,severe,life_threatening'],
            'symptoms'      => ['required', 'string'],
            'blood_unit_id' => ['nullable', 'exists:blood_units,id'],
            'action_taken'  => ['nullable', 'string'],
            'outcome'       => ['nullable', 'string', 'max:255'],
            'notes'         => ['nullable', 'string'],
        ];
    }
}
