@extends('layouts.hospital')

@section('title', 'Submit Requisition')
@section('page_title', 'Create Blood Requisition')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
    <h2 class="text-lg font-bold text-gray-900 mb-6">New Clinical Blood Requisition</h2>

    <form method="POST" action="{{ route('hospital.requests.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Select Patient *</label>
                <select name="patient_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Choose Patient from Directory...</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                            {{ $patient->name }} (MRN: {{ $patient->mrn }}) - Blood: {{ $patient->bloodGroup->name ?? 'Unspecified' }}
                        </option>
                    @endforeach
                </select>
                @error('patient_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Required Blood Group *</label>
                <select name="blood_group" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Blood Group...</option>
                    @foreach($bloodGroups as $group)
                        <option value="{{ $group->name }}" {{ old('blood_group') === $group->name ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
                @error('blood_group') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Units Needed (Bags) *</label>
                <input type="number" name="units_needed" value="{{ old('units_needed', 1) }}" min="1" max="50" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('units_needed') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Urgency Priority *</label>
                <select name="urgency" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="routine" {{ old('urgency') === 'routine' ? 'selected' : '' }}>Routine</option>
                    <option value="urgent" {{ old('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="emergency" {{ old('urgency') === 'emergency' || !old('urgency') ? 'selected' : '' }}>Emergency</option>
                </select>
                @error('urgency') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Clinical Indication / Reason</label>
                <textarea name="reason" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Specify clinical indication, surgery type, or emergency notes...">{{ old('reason') }}</textarea>
                @error('reason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('hospital.requests.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold rounded-lg text-sm transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">Submit Requisition</button>
        </div>
    </form>
</div>
@endsection
