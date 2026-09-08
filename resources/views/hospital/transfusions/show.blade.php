@extends('layouts.hospital')

@section('title', 'Transfusion Administration #TR-' . $transfusion->id)

@section('content')
<div class="space-y-6 max-w-5xl pb-12">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('hospital.dashboard')],
        ['label' => 'Transfusions Log', 'url' => route('hospital.transfusions.index')],
        ['label' => '#TR-' . $transfusion->id]
    ]" />

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Transfusion Administration #TR-{{ $transfusion->id }}</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Patient: <strong class="text-slate-900 dark:text-slate-100">{{ $transfusion->patient->name }}</strong> (MRN: {{ $transfusion->patient->mrn }})</p>
        </div>
        <div>
            <x-status-badge :status="$transfusion->status" />
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-6">
        
        <!-- Workflow Action Controls Bar -->
        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700/60 flex flex-wrap items-center gap-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mr-2">Workflow Controls:</span>
            
            @if($transfusion->status === 'scheduled')
                <form method="POST" action="{{ route('hospital.transfusions.start', $transfusion->id) }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                    @csrf
                    <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-sm inline-flex items-center gap-1">
                        <span>▶ Start Transfusion</span>
                    </button>
                </form>
            @elseif($transfusion->status === 'issued' || $transfusion->status === 'started')
                @if($transfusion->status === 'issued')
                    <form method="POST" action="{{ route('hospital.transfusions.start', $transfusion->id) }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                        @csrf
                        <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-sm inline-flex items-center gap-1">
                            <span>▶ Start Transfusion</span>
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('hospital.transfusions.complete', $transfusion->id) }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                    @csrf
                    <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-sm inline-flex items-center gap-1">
                        <span>✓ Mark Completed</span>
                    </button>
                </form>
                <form method="POST" action="{{ route('hospital.transfusions.stop', $transfusion->id) }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="reason" placeholder="Reason to stop..." required class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none" />
                    <button type="submit" :disabled="isSubmitting" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Stop Transfusion
                    </button>
                </form>
            @else
                <span class="text-xs text-slate-500 dark:text-slate-400 italic font-medium">Transfusion state: {{ ucfirst($transfusion->status) }}</span>
            @endif
        </div>

        <!-- Issued Blood Units Table -->
        <div class="space-y-3">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">Issued Units & Cross-match Verification</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th scope="col" class="px-4 py-3">Unit Barcode</th>
                            <th scope="col" class="px-4 py-3">Blood Group</th>
                            <th scope="col" class="px-4 py-3">Expiry Date</th>
                            <th scope="col" class="px-4 py-3">Unit Disposition</th>
                            <th scope="col" class="px-4 py-3">Unit Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                        @forelse($transfusion->transfusionUnits as $tu)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">{{ $tu->bloodUnit->unit_number }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-0.5 bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 text-xs font-bold rounded-md">
                                        {{ $tu->bloodUnit->bloodGroup->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $tu->bloodUnit->expiry_date }}</td>
                                <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300 font-semibold">
                                    <span>{{ ucfirst($tu->disposition ?? 'Issued Unit') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$tu->bloodUnit->status" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400 italic">No blood units assigned to this transfusion order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Report Adverse Reaction Section -->
        <div class="p-5 bg-rose-50/60 dark:bg-rose-950/30 rounded-2xl border border-rose-200 dark:border-rose-900/50 space-y-4">
            <div>
                <h3 class="text-sm font-extrabold text-rose-900 dark:text-rose-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Report Transfusion Reaction & Clinical Safety Incident</span>
                </h3>
                <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5">Log adverse reactions immediately to notify blood bank administration and pause unit distribution.</p>
            </div>

            <form method="POST" action="{{ route('hospital.transfusions.reaction', $transfusion->id) }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="reaction_type" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Reaction Type *</label>
                        <input type="text" id="reaction_type" name="reaction_type" required placeholder="Febrile, Allergic, Hemolytic..." class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none" />
                    </div>
                    <div>
                        <label for="severity" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Severity Level *</label>
                        <select id="severity" name="severity" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-xs font-semibold focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            <option value="mild">Mild (Localized rash/fever)</option>
                            <option value="moderate">Moderate (Systemic reaction)</option>
                            <option value="severe">Severe (Severe hypotension/hemolysis)</option>
                            <option value="life_threatening">Life Threatening (Anaphylaxis/TRALI)</option>
                        </select>
                    </div>
                    <div>
                        <label for="blood_unit_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Associated Unit (Optional)</label>
                        <select id="blood_unit_id" name="blood_unit_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-xs font-semibold focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            <option value="">-- All / Unspecified --</option>
                            @foreach($transfusion->transfusionUnits as $tu)
                                <option value="{{ $tu->blood_unit_id }}">{{ $tu->bloodUnit->unit_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="symptoms" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Observed Symptoms & Intervention Notes *</label>
                    <textarea id="symptoms" name="symptoms" required rows="2" placeholder="Fever, chills, rash, dyspnea, treatment administered..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                </div>
                <button type="submit" :disabled="isSubmitting" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-sm transition disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Submitting Reaction...' : 'Submit Reaction & Alert Admin'">Submit Reaction & Alert Admin</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
