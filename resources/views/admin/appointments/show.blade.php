@extends('layouts.admin')

@section('title', 'Donation Intake Workflow')
@section('page_title', 'Donation Processing Workflow')

@section('content')
<div class="max-w-4xl space-y-6 pb-12" 
     x-data="{ 
         isSubmitting: false,
         showReviewModal: false,
         volumeMl: '{{ old('volume_ml', '') }}',
         storageLocation: '{{ old('storage_location', 'Central Blood Bank Storage Room A - Refrigerator #2') }}',
         selectedComponentText: 'Whole Blood (WB)',
         
         updateComponentText() {
             const select = $refs.componentSelect;
             if (select && select.options[select.selectedIndex]) {
                 this.selectedComponentText = select.options[select.selectedIndex].text;
             }
         },
         
         handleFormSubmit(e) {
             e.preventDefault();
             this.updateComponentText();
             this.showReviewModal = true;
         },
         
         confirmIntakeAndSubmit() {
             this.isSubmitting = true;
             $refs.intakeForm.submit();
         }
     }">
    <!-- Header & Action Row -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $appointment->donor->user->name ?? 'Donor #' . $appointment->donor_id }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-mono font-extrabold bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                    {{ $appointment->donor->bloodGroup->name ?? 'Unspecified' }}
                </span>
            </div>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1 font-mono">Appointment #{{ $appointment->id }} &bull; Scheduled for {{ $appointment->appointment_date }} ({{ $appointment->appointment_time ?? '09:00 AM' }})</p>
        </div>
        <div class="flex items-center gap-2">
            <x-status-badge :status="$appointment->status" />
            <a href="{{ route('admin.appointments.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
                Back to List
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('error'))
        <div class="p-4 text-xs md:text-sm text-rose-900 dark:text-rose-200 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 shadow-sm" role="alert">
            <span class="font-bold">Error:</span> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 text-xs md:text-sm text-rose-900 dark:text-rose-200 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 shadow-sm" role="alert">
            <div class="font-bold mb-1">Please correct the following intake errors:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 3-Step Donation Stepper Header -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 md:p-8 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
            <h2 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span>Donation Processing Workflow</span>
            </h2>
            <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400">Standard Operational Intake</span>
        </div>

        <!-- Stepper Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#111c38] border border-slate-200 dark:border-slate-800 space-y-1">
                <div class="flex items-center gap-2 text-xs font-extrabold uppercase text-slate-600 dark:text-slate-400">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-[10px]">1</span>
                    <span>Donor Screening</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">Vitals pre-screening check and donor identity verification.</p>
            </div>

            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 space-y-1">
                <div class="flex items-center gap-2 text-xs font-extrabold uppercase text-rose-700 dark:text-rose-300">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-white text-[10px]">2</span>
                    <span>Donation Intake</span>
                </div>
                <p class="text-[11px] text-rose-800 dark:text-rose-200 leading-relaxed">Blood unit collection, volume measurement (mL), and component tagging.</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#111c38] border border-slate-200 dark:border-slate-800 space-y-1">
                <div class="flex items-center gap-2 text-xs font-extrabold uppercase text-slate-600 dark:text-slate-400">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-[10px]">3</span>
                    <span>Inventory Recording</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">Generate unit number, update central inventory stock (+1), and update donor interval record.</p>
            </div>
        </div>

        @if($appointment->status === 'completed')
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 space-y-1 shadow-sm">
                <div class="flex items-center gap-2 font-extrabold text-xs md:text-sm">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Donation Intake Completed</span>
                </div>
                <p class="text-xs text-emerald-700 dark:text-emerald-300">This appointment has been marked as Completed and the blood unit bag was recorded in central inventory.</p>
            </div>
        @else
            <!-- Clinical Donation Intake Form -->
            <form x-ref="intakeForm" 
                  method="POST" 
                  action="{{ route('admin.appointments.intake', $appointment->id) }}" 
                  class="space-y-6 pt-4 border-t border-slate-100 dark:border-slate-800" 
                  @submit="handleFormSubmit($event)">
                @csrf
                
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400">Complete Donation Unit Collection</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Donor Name (Auto-Filled)</label>
                        <input type="text" value="{{ $appointment->donor->user->name ?? 'Donor' }}" readonly class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 px-3.5 py-2.5 text-sm font-bold cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">ABO/Rh Profile (Auto-Filled)</label>
                        <input type="text" value="{{ $appointment->donor->bloodGroup->name ?? 'Unspecified' }}" readonly class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 text-rose-600 dark:text-rose-400 px-3.5 py-2.5 text-sm font-black cursor-not-allowed font-mono">
                    </div>

                    <div>
                        <label for="component_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Blood Component Type <span class="text-rose-600">*</span></label>
                        <select id="component_id" name="component_id" x-ref="componentSelect" @change="updateComponentText()" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            @foreach($components as $component)
                                <option value="{{ $component->id }}" {{ $component->name === 'Whole Blood' ? 'selected' : '' }}>
                                    {{ $component->name }} ({{ $component->code }}) &bull; Shelf Life: {{ $component->shelf_life_days }} Days
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="volume_ml" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Collection Volume (mL) <span class="text-rose-600">*</span></label>
                        <input id="volume_ml" type="number" name="volume_ml" x-model="volumeMl" min="200" max="600" placeholder="e.g. 450" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none font-mono">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Accepted range: 200 mL – 600 mL.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="storage_location" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Storage Location <span class="text-rose-600">*</span></label>
                        <input id="storage_location" type="text" name="storage_location" x-model="storageLocation" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Submitting will open final review dialog before adding unit to inventory.</span>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-rose-500 inline-flex items-center gap-1.5">
                        <span>Review & Complete Intake</span>
                    </button>
                </div>
            </form>
        @endif
    </div>

    <!-- Final Intake Safety Review Dialog -->
    <div x-show="showReviewModal" 
         x-cloak 
         @keydown.escape.window="showReviewModal = false"
         @keydown.tab.prevent="
             const focusables = $el.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex=\'-1\'])');
             const first = focusables[0];
             const last = focusables[focusables.length - 1];
             if ($event.shiftKey && document.activeElement === first) { last.focus(); }
             else if (!$event.shiftKey && document.activeElement === last) { first.focus(); }
         "
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" 
         role="dialog" 
         aria-modal="true">
        
        <div class="w-full max-w-lg bg-white dark:bg-[#0c1427] rounded-2xl border border-rose-300 dark:border-rose-800/80 shadow-2xl p-6 text-left space-y-4 max-h-[85vh] flex flex-col" @click.away="showReviewModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 shrink-0">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Review Donation Intake Submission</span>
                </h3>
                <button type="button" @click="showReviewModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4 overflow-y-auto pr-1">
                <p class="text-xs text-slate-600 dark:text-slate-300">Verify donation intake parameters before submitting unit to inventory:</p>
                
                <div class="bg-slate-50 dark:bg-[#111c38] rounded-xl p-4 border border-slate-200 dark:border-slate-800 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">Donor:</span>
                        <span class="font-extrabold text-slate-900 dark:text-white">{{ $appointment->donor->user->name ?? 'Donor' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">Appointment Context:</span>
                        <span class="font-mono text-slate-700 dark:text-slate-300">Appointment #{{ $appointment->id }} ({{ $appointment->appointment_date }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">ABO/Rh Profile:</span>
                        <span class="font-mono font-black text-rose-600 dark:text-rose-400">{{ $appointment->donor->bloodGroup->name ?? 'Unspecified' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">Selected Component:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedComponentText"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">Collection Volume:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white"><span x-text="volumeMl || '0'"></span> mL</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">Storage Location:</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200" x-text="storageLocation"></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 shrink-0">
                <button type="button" @click="showReviewModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">Back to Form</button>
                <button type="button" 
                        :disabled="isSubmitting"
                        @click="confirmIntakeAndSubmit()" 
                        class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Recording Intake...' : 'Confirm Intake & Add to Inventory'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
