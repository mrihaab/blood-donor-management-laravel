@extends('layouts.hospital')

@section('title', 'Register Patient')

@section('content')
<div class="max-w-4xl space-y-6 pb-12">

    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('hospital.dashboard')],
        ['label' => 'Inpatient Directory', 'url' => route('hospital.patients.index')],
        ['label' => 'Register Inpatient']
    ]" />

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Inpatient Admission Registration</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Register new patient medical record details for bedside verification and blood component orders.</p>
        </div>
    </div>

    <!-- High-Visibility Validation Errors Summary -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/90 border-l-4 border-rose-500 text-rose-800 dark:text-rose-200 shadow-md space-y-2" role="alert">
            <div class="flex items-center gap-2 font-bold text-sm">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Please correct the following errors before submitting:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Container Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 shadow-sm transition-colors relative">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-6">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">Patient Clinical Information</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Medical Record Number (MRN) and bedside delivery location.</p>
        </div>

        <form method="POST" action="{{ route('hospital.patients.store') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="space-y-6">
            @csrf

            <!-- Section 1: Identity & Demographics -->
            <div class="space-y-4">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400">1. Identity & Demographics</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Patient Name -->
                    <div>
                        <label for="patient_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Full Patient Name <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="text" id="patient_name" name="name" value="{{ old('name') }}" placeholder="e.g. Umar Zulfiqar" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none placeholder-slate-400 dark:placeholder-slate-500 transition shadow-sm">
                        @error('name') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- MRN Number -->
                    <div>
                        <label for="mrn" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Medical Record Number (MRN) <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="text" id="mrn" name="mrn" value="{{ old('mrn') }}" placeholder="e.g. MRN-908123" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none placeholder-slate-400 dark:placeholder-slate-500 transition shadow-sm font-mono">
                        @error('mrn') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Gender <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <select id="gender" name="gender" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition shadow-sm">
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Date of Birth <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition shadow-sm font-mono">
                        @error('date_of_birth') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Blood & Contact Information -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400">2. Blood & Contact Profile</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Blood Group -->
                    <div>
                        <label for="blood_group_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Blood Group (Optional)
                        </label>
                        <select id="blood_group_id" name="blood_group_id" class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition shadow-sm">
                            <option value="">Select Blood Group...</option>
                            @foreach($bloodGroups as $group)
                                <option value="{{ $group->id }}" {{ old('blood_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('blood_group_id') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Contact Phone -->
                    <div>
                        <label for="contact_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Contact Phone
                        </label>
                        <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" placeholder="e.g. +92 300 1234567" class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none placeholder-slate-400 dark:placeholder-slate-500 transition shadow-sm font-mono">
                        @error('contact_number') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Bedside Location -->
            <div class="bg-slate-50 dark:bg-[#070d1a] p-4 md:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-emerald-800 dark:text-emerald-400 flex items-center gap-1.5">
                    <span>📍</span> Inpatient Bedside Location (Auto-fills Requisitions)
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="ward_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ward Name</label>
                        <input type="text" id="ward_name" name="ward_name" value="{{ old('ward_name') }}" placeholder="e.g. ICU Ward 3" class="w-full px-3.5 py-2 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                        @error('ward_name') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="room_number" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Room Number</label>
                        <input type="text" id="room_number" name="room_number" value="{{ old('room_number') }}" placeholder="e.g. Room 304" class="w-full px-3.5 py-2 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                        @error('room_number') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="bed_number" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Bed Number</label>
                        <input type="text" id="bed_number" name="bed_number" value="{{ old('bed_number') }}" placeholder="e.g. Bed B-12" class="w-full px-3.5 py-2 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                        @error('bed_number') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions Footer -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('hospital.patients.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs transition">Cancel</a>
                <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-lg shadow-blue-600/30 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Saving Record...' : 'Save Inpatient Record →'">Save Inpatient Record &rarr;</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
