<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'donor_id' => 'required|exists:donors,id',
            'quantity' => 'required|integer|min:100|max:1000',
            'donation_date' => 'required|date|before_or_equal:today',
            'collection_center' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
