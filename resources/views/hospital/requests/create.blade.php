@extends('layouts.hospital')

@section('title', 'Submit Requisition')
@section('page_title', 'Create Blood Requisition')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">

    <!-- Header Actions with Back Link -->
    <div class="flex items-center justify-between">
        <a href="{{ route('hospital.requests.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Requisition Stream</span>
        </a>
        <x-status-badge status="approved" label="FEFO Blood Order" />
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
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">New Clinical Blood Component Requisition</h2>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Submit blood component requisitions with bedside location verification and emergency triage routing.</p>
        </div>

        <form method="POST" action="{{ route('hospital.requests.store') }}" class="space-y-6">
            @csrf

            <!-- 2-Column Responsive Fieldset Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Select Patient -->
                <div class="md:col-span-2">
                    <label for="patient_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Select Patient from Inpatient Registry <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="patient_id" name="patient_id" required class="w-full px-4 py-2.5 rounded-xl text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        <option value="">Choose Patient from Directory...</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
                                {{ $patient->name }} (MRN: {{ $patient->mrn }}) - Blood: {{ $patient->bloodGroup->name ?? 'Unspecified' }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Required Blood Group -->
                <div>
                    <label for="blood_group" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Required Blood Group <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="blood_group" name="blood_group" required class="w-full px-4 py-2.5 rounded-xl text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        <option value="">Select Blood Group...</option>
                        @foreach($bloodGroups as $group)
                            <option value="{{ $group->name }}" {{ old('blood_group') === $group->name ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                    @error('blood_group') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Units Needed -->
                <div>
                    <label for="units_needed" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Units Needed (Bags) <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <input id="units_needed" type="number" name="units_needed" value="{{ old('units_needed', 1) }}" min="1" max="50" required class="w-full px-4 py-2.5 rounded-xl text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                    @error('units_needed') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Urgency Priority -->
                <div class="md:col-span-2">
                    <label for="urgency" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Urgency Triage Priority <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="urgency" name="urgency" required class="w-full px-4 py-2.5 rounded-xl text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        <option value="routine" {{ old('urgency', request('urgency')) === 'routine' ? 'selected' : '' }}>Routine Schedule</option>
                        <option value="urgent" {{ old('urgency', request('urgency')) === 'urgent' ? 'selected' : '' }}>Urgent High Priority</option>
                        <option value="emergency" {{ old('urgency', request('urgency', 'emergency')) === 'emergency' ? 'selected' : '' }}>STAT Emergency (Notifies On-Call Vault Officer)</option>
                    </select>
                    @error('urgency') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Patient Location Box -->
                <div class="md:col-span-2 bg-slate-50 dark:bg-[#070d1a] p-4 md:p-5 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Bedside Clinical Verification & Location</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="ward_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ward Name</label>
                            <input id="ward_name" type="text" name="ward_name" value="{{ old('ward_name') }}" placeholder="e.g. ICU Ward 3" class="w-full px-3.5 py-2 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                            @error('ward_name') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="room_number" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Room Number</label>
                            <input id="room_number" type="text" name="room_number" value="{{ old('room_number') }}" placeholder="e.g. Room 304" class="w-full px-3.5 py-2 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                            @error('room_number') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="bed_number" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Bed Number</label>
                            <input id="bed_number" type="text" name="bed_number" value="{{ old('bed_number') }}" placeholder="e.g. Bed B-12" class="w-full px-3.5 py-2 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                            @error('bed_number') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Family Attendant Details -->
                <div class="md:col-span-2 bg-slate-50 dark:bg-[#070d1a] p-4 md:p-5 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Patient Family Attendant (Replacement SMS Dispatch)</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="attendant_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Attendant Name</label>
                            <input id="attendant_name" type="text" name="attendant_name" value="{{ old('attendant_name') }}" placeholder="e.g. Chaudhry Tariq (Brother)" class="w-full px-3.5 py-2 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                            @error('attendant_name') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="attendant_phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Attendant Phone Number</label>
                            <input id="attendant_phone" type="text" name="attendant_phone" value="{{ old('attendant_phone') }}" placeholder="e.g. 0300-1122334" class="w-full px-3.5 py-2 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Direct replacement SMS dispatched if vault stock falls below reserve thresholds.</span>
                            @error('attendant_phone') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Clinical Reason -->
                <div class="md:col-span-2">
                    <label for="reason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Clinical Indication / Emergency Diagnosis Notes
                    </label>
                    <textarea id="reason" name="reason" rows="3" class="w-full px-4 py-2.5 rounded-xl text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none placeholder-slate-400 dark:placeholder-slate-500 transition shadow-sm" placeholder="Specify clinical indication, surgery type, or emergency notes...">{{ old('reason') }}</textarea>
                    @error('reason') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Sticky Form Actions Bar -->
            <div class="sticky bottom-0 bg-white/95 dark:bg-[#0c1427]/95 backdrop-blur border-t border-slate-200 dark:border-slate-800 -mx-6 -mb-6 md:-mx-8 md:-mb-8 p-4 md:px-8 flex items-center justify-end gap-3 rounded-b-2xl z-10 shadow-lg">
                <a href="{{ route('hospital.requests.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-md focus:outline-none focus:ring-2 focus:ring-rose-500">
                    Submit Requisition to Central Vault &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
