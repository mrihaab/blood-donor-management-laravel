@extends('layouts.hospital')

@section('title', 'Edit Patient')
@section('page_title', 'Edit Patient Info')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
    <h2 class="text-lg font-bold text-gray-900 mb-6">Update Patient Records ({{ $patient->name }})</h2>

    <form method="POST" action="{{ route('hospital.patients.update', $patient->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Full Patient Name *</label>
                <input type="text" name="name" value="{{ old('name', $patient->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Medical Record Number (MRN) *</label>
                <input type="text" name="mrn" value="{{ old('mrn', $patient->mrn) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('mrn') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Gender *</label>
                <select name="gender" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ old('gender', $patient->gender) === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Date of Birth *</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('date_of_birth') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Blood Group</label>
                <select name="blood_group_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Blood Group...</option>
                    @foreach($bloodGroups as $group)
                        <option value="{{ $group->id }}" {{ old('blood_group_id', $patient->blood_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
                @error('blood_group_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Contact Phone</label>
                <input type="text" name="contact_number" value="{{ old('contact_number', $patient->contact_number) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('contact_number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('hospital.patients.show', $patient->id) }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold rounded-lg text-sm transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">Update Patient</button>
        </div>
    </form>
</div>
@endsection
