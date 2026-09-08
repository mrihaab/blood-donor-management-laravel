@extends('layouts.admin')

@section('title', 'Emergency Requisition Queue')

@section('content')
<div class="space-y-6">
    <!-- Header Title -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Emergency Operations & Priority Triage Queue</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Priority-ranked clinical requisitions sorted by Urgency Level and Required-By timeframe.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-[#0c1427] p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.emergency_requests.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <div>
                <label for="urgency-filter" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Urgency Priority</label>
                <select id="urgency-filter" name="urgency" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-xl text-xs font-semibold focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="">All Priorities</option>
                    <option value="emergency" {{ request('urgency') === 'emergency' ? 'selected' : '' }}>STAT Emergency</option>
                    <option value="urgent" {{ request('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="routine" {{ request('urgency') === 'routine' ? 'selected' : '' }}>Routine</option>
                </select>
            </div>

            <div>
                <label for="blood-group-filter" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Blood Group</label>
                <select id="blood-group-filter" name="blood_group" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-xl text-xs font-semibold focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="">All Blood Groups</option>
                    @foreach($bloodGroups as $group)
                        <option value="{{ $group->name }}" {{ request('blood_group') === $group->name ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status-filter" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                <select id="status-filter" name="status" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-xl text-xs font-semibold focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="dispensed" {{ request('status') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div>
                <label for="hospital-filter" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Hospital</label>
                <select id="hospital-filter" name="hospital_id" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-xl text-xs font-semibold focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="">All Hospitals</option>
                    @foreach($hospitals as $hosp)
                        <option value="{{ $hosp->id }}" {{ request('hospital_id') == $hosp->id ? 'selected' : '' }}>{{ $hosp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <a href="{{ route('admin.emergency_requests.index') }}" class="w-full text-center px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold rounded-xl text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Reset Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Requisition Queue Table -->
    <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Req #</th>
                        <th class="px-4 py-3">Hospital</th>
                        <th class="px-4 py-3">Patient</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Quantity</th>
                        <th class="px-4 py-3">Required By</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 {{ $req->urgency_level === 'emergency' ? 'bg-rose-50/30 dark:bg-rose-950/20' : '' }} transition">
                            <td class="px-4 py-3">
                                <x-status-badge :status="$req->urgency_level" />
                            </td>
                            <td class="px-4 py-3 font-extrabold text-slate-900 dark:text-white">#REQ-{{ $req->id }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">{{ $req->hospitalEntity->name ?? $req->hospital }}</td>
                            <td class="px-4 py-3 text-slate-800 dark:text-slate-200">{{ $req->patient->name ?? $req->patient_name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-black rounded-lg shadow-sm">
                                    {{ $req->blood_group }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-extrabold text-slate-900 dark:text-white">{{ $req->units_needed }} Bag(s)</td>
                            <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-400">
                                {{ $req->required_by ? $req->required_by->format('M d, Y H:i') : 'ASAP' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-status-badge :status="$req->status" />
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if($req->status === 'dispensed')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-xs font-bold rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-sm whitespace-nowrap">
                                        ✓ Issued & Dispensed
                                    </span>
                                @elseif($req->fefo_unit)
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap" 
                                         x-data="{ 
                                             showModal: false, 
                                             triggerEl: null, 
                                             isSubmitting: false,
                                             openModal() {
                                                 this.triggerEl = document.activeElement;
                                                 this.isSubmitting = false;
                                                 this.showModal = true;
                                                 $nextTick(() => { $refs.confirmDispatchBtn?.focus(); });
                                             },
                                             closeModal() {
                                                 this.showModal = false;
                                                 if (this.triggerEl) { this.triggerEl.focus(); }
                                             },
                                             submitDispatch() {
                                                 if (this.isSubmitting) return;
                                                 this.isSubmitting = true;
                                                 $refs.dispatchForm.submit();
                                             }
                                         }">
                                        
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-mono font-bold text-purple-800 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 border border-purple-200 dark:border-purple-800 rounded-lg" title="Current FEFO Candidate Bag #{{ $req->fefo_unit->unit_number }}">
                                            FEFO Candidate: #{{ $req->fefo_unit->unit_number }}
                                        </span>
                                        
                                        <!-- 2-Step Confirmation Dispatch Trigger Button -->
                                        <button type="button" 
                                                @click="openModal()"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                            Issue FEFO Unit
                                        </button>

                                        <!-- 2-Step Confirmation Dispatch Modal -->
                                        <div x-show="showModal" 
                                             x-cloak 
                                             @keydown.escape.window="closeModal()"
                                             @keydown.tab.prevent="
                                                 const focusables = $el.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex=\'-1\'])');
                                                 const first = focusables[0];
                                                 const last = focusables[focusables.length - 1];
                                                 if ($event.shiftKey && document.activeElement === first) { last.focus(); }
                                                 else if (!$event.shiftKey && document.activeElement === last) { first.focus(); }
                                             "
                                             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4" 
                                             role="dialog" 
                                             aria-modal="true"
                                             aria-labelledby="modal-title-{{ $req->id }}">
                                            
                                            <div class="w-full max-w-lg bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-left space-y-4" @click.away="closeModal()">
                                                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                                    <h3 id="modal-title-{{ $req->id }}" class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>Confirm FEFO Unit Dispatch</span>
                                                    </h3>
                                                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500 rounded-lg">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>

                                                <div class="space-y-3 text-xs md:text-sm text-slate-700 dark:text-slate-300">
                                                    <div class="bg-slate-50 dark:bg-[#070d1a] p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                                                        <p><strong>Requisition:</strong> #REQ-{{ $req->id }}</p>
                                                        <p><strong>Hospital:</strong> {{ $req->hospitalEntity->name ?? $req->hospital }}</p>
                                                        <p><strong>Patient:</strong> {{ $req->patient->name ?? $req->patient_name }}</p>
                                                        <p><strong>Blood Group Required:</strong> <span class="font-bold text-rose-600 dark:text-rose-400">{{ $req->blood_group }}</span> ({{ $req->units_needed }} Bag)</p>
                                                    </div>

                                                    <!-- Current FEFO Candidate Wording -->
                                                    <div class="p-3 bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 rounded-xl text-purple-900 dark:text-purple-200 text-xs">
                                                        <p class="font-bold">Current FEFO Candidate: Bag #{{ $req->fefo_unit->unit_number }} — Expires {{ $req->fefo_unit->expiry_date }}</p>
                                                    </div>

                                                    <!-- Explicit Transaction Notice Wording -->
                                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed italic">
                                                        Final unit allocation is performed by the server at dispatch time using current compatible inventory and FEFO rules. The final allocated unit may differ if inventory changes.
                                                    </p>
                                                </div>

                                                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                                                    <button type="button" @click="closeModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition focus:outline-none focus:ring-2 focus:ring-slate-400">
                                                        Cancel
                                                    </button>
                                                    <form x-ref="dispatchForm" method="POST" action="{{ route('admin.blood_requests.instant_dispense', $req->id) }}" class="inline">
                                                        @csrf
                                                        <button type="button" 
                                                                x-ref="confirmDispatchBtn"
                                                                :disabled="isSubmitting"
                                                                @click="submitDispatch()" 
                                                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-1.5">
                                                            <template x-if="isSubmitting">
                                                                <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                            </template>
                                                            <span x-text="isSubmitting ? 'Dispatching...' : 'Confirm Dispatch & Fulfill Requisition'"></span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        @if(isset($req->o_neg_stock_count) && $req->o_neg_stock_count > 0)
                                            <form method="POST" action="{{ route('admin.blood_requests.dispense_universal_fallback', $req->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" title="Emergency Release of Universal O- Negative blood bag ({{ $req->o_neg_stock_count }} Bags Available)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-amber-500">
                                                    Issue O- Fallback ({{ $req->o_neg_stock_count }})
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.blood_requests.notify_donors', $req->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" title="Dispatch alerts to Voluntary Donors" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-rose-500">
                                                Cascade Donors
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-500 italic">No requisitions currently in priority queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
