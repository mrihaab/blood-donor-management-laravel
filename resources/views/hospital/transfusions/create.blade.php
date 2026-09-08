@extends('layouts.hospital')

@section('title', 'Schedule Clinical Transfusion')

@section('content')
<div class="max-w-4xl space-y-6 pb-12">

    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('hospital.dashboard')],
        ['label' => 'Transfusions Log', 'url' => route('hospital.transfusions.index')],
        ['label' => 'Schedule Transfusion']
    ]" />

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Schedule Clinical Transfusion</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Assign approved blood requisitions for bedside administration and monitoring.</p>
        </div>
    </div>

    <!-- Form Container Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 shadow-sm transition-colors relative">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-6">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">Transfusion Setup Details</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Select an approved blood requisition and recipient patient.</p>
        </div>

        <form method="POST" action="{{ route('hospital.transfusions.store') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="space-y-6">
            @csrf

            <div class="space-y-6">
                <!-- Approved Requisition -->
                <div>
                    <label for="blood_request_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Approved Requisition <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="blood_request_id" name="blood_request_id" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition shadow-sm">
                        <option value="">-- Select Approved Requisition --</option>
                        @foreach($approvedRequests as $req)
                            <option value="{{ $req->id }}">
                                #REQ-{{ $req->id }} - Patient: {{ $req->patient_name }} ({{ $req->blood_group }}, {{ $req->units_needed }} units)
                            </option>
                        @endforeach
                    </select>
                    @error('blood_request_id') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Target Patient -->
                <div>
                    <label for="patient_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Target Recipient Patient <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="patient_id" name="patient_id" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition shadow-sm">
                        <option value="">-- Select Patient --</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}">
                                {{ $patient->name }} (MRN: {{ $patient->mrn }}, Blood Group: {{ $patient->bloodGroup->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Clinical Notes -->
                <div>
                    <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Pre-Transfusion Clinical Notes
                    </label>
                    <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none placeholder-slate-400 dark:placeholder-slate-500 transition shadow-sm" placeholder="Specify pre-transfusion vitals, ward location, special instructions..."></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('hospital.transfusions.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs transition">
                    Cancel
                </a>
                <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-lg shadow-blue-600/30 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Scheduling...' : 'Schedule Transfusion →'">Schedule Transfusion &rarr;</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
