@extends('layouts.donor')

@section('title', 'Request Blood')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex items-center justify-between transition-colors">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Submit Family & Emergency Blood Request</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Request blood component support for a patient or family member in care.</p>
        </div>
        <a href="{{ route('donor.blood_requests.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition border border-slate-200 dark:border-slate-700">
            &larr; Back
        </a>
    </div>

    <!-- Error Summary Block -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/90 border-l-4 border-rose-500 text-rose-800 dark:text-rose-200 shadow-sm space-y-1.5" role="alert">
            <div class="flex items-center gap-2 font-bold text-xs md:text-sm">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Please correct the highlighted issues:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 shadow-sm transition-colors">
        <form method="POST" action="{{ route('donor.blood_requests.store') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Patient Name -->
                <div>
                    <label for="patient_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Patient Full Name <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <input id="patient_name" type="text" name="patient_name" value="{{ old('patient_name') }}" placeholder="e.g. Zainab Bibi" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                    @error('patient_name') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Blood Group Needed -->
                <div>
                    <label for="blood_group_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Required Blood Group <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="blood_group_id" name="blood_group_id" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        <option value="">Select Blood Group...</option>
                        @foreach($bloodGroups as $group)
                            <option value="{{ $group->id }}" {{ old('blood_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                    @error('blood_group_id') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Units Needed -->
                <div>
                    <label for="units_needed" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Units Needed (Bags) <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <input id="units_needed" type="number" name="units_needed" value="{{ old('units_needed', 1) }}" min="1" max="20" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm font-mono">
                    @error('units_needed') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Urgency Level -->
                <div>
                    <label for="urgency" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Urgency Level <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="urgency" name="urgency" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        <option value="normal" {{ old('urgency') === 'normal' ? 'selected' : '' }}>Normal Schedule</option>
                        <option value="urgent" {{ old('urgency') === 'urgent' ? 'selected' : '' }}>Urgent Priority</option>
                        <option value="emergency" {{ old('urgency') === 'emergency' ? 'selected' : '' }}>Critical Emergency</option>
                    </select>
                    @error('urgency') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Hospital Name & Location -->
            <div>
                <label for="hospital_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Hospital Name & Address <span class="text-rose-600 font-bold">*</span>
                </label>
                <input id="hospital_name" type="text" name="hospital_name" value="{{ old('hospital_name') }}" placeholder="e.g. City General Hospital, Ward 4" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                @error('hospital_name') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Contact Number -->
            <div>
                <label for="contact_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Contact Phone Number <span class="text-rose-600 font-bold">*</span>
                </label>
                <input id="contact_number" type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="e.g. +92 300 1234567" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm font-mono">
                @error('contact_number') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Reason / Notes -->
            <div>
                <label for="reason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Clinical Context / Reason (Optional)
                </label>
                <textarea id="reason" name="reason" rows="3" placeholder="Provide medical context or procedure details..." class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">{{ old('reason') }}</textarea>
                @error('reason') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('donor.blood_requests.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" :disabled="isSubmitting" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs md:text-sm font-extrabold rounded-xl shadow-md transition flex items-center gap-2">
                    <span x-text="isSubmitting ? 'Submitting...' : 'Submit Request'">Submit Request</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
