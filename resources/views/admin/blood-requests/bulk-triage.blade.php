@extends('layouts.admin')

@section('title', 'High-Volume Batch Triage Queue')
@section('page_title', 'High-Volume Batch Triage & Allocation Engine')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Banner -->
    <div class="rounded-2xl bg-slate-900 dark:bg-[#070d1a] p-6 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 border border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <x-status-badge status="approved" label="Rule-Based FEFO Batch Engine" />
                <span class="text-xs text-slate-400 font-mono">100-200 Patient Scalability Queue</span>
            </div>
            <h2 class="text-xl font-extrabold text-white mt-2">Central Hospital Batch Triage Matrix</h2>
            <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                Scans pending hospital requisitions, sorts by clinical triage priority (Emergency > Urgent > Routine), checks unexpired FEFO ABO compatible stock, and executes batch allocation.
            </p>
        </div>

        <div class="flex items-center gap-4 bg-slate-950/80 p-4 rounded-xl border border-slate-800 text-center shrink-0">
            <div>
                <span class="block text-2xl font-black text-emerald-400">{{ $readyToDispenseCount }}</span>
                <span class="text-[10px] text-slate-400 uppercase font-bold">Ready to Dispense</span>
            </div>
            <div class="h-8 w-px bg-slate-800"></div>
            <div>
                <span class="block text-2xl font-black text-rose-400">{{ $shortageCount }}</span>
                <span class="text-[10px] text-slate-400 uppercase font-bold">Stock Depleted</span>
            </div>
        </div>
    </div>

    <!-- Requisition Queue & Batch Form -->
    <form id="bulk-triage-form" method="POST" action="{{ route('admin.blood_requests.bulk_dispense') }}" x-data="{ selected: [], isSubmitting: false, showConfirmModal: false }">
        @csrf

        <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Pending Requisitions Queue ({{ $triagedRequests->count() }} Total)</span>
                </h3>

                @if($readyToDispenseCount > 0)
                    <button type="button" 
                            x-show="selected.length > 0" 
                            @click="showConfirmModal = true"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 whitespace-nowrap">
                        <span>Approve & Dispense Selected Bags (<span x-text="selected.length"></span>) &rarr;</span>
                    </button>
                @endif
            </div>

            <!-- Confirmation Modal for Bulk Action -->
            <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="w-full max-w-lg bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-left space-y-4" @click.away="showConfirmModal = false">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Confirm Bulk Requisition Allocation</span>
                        </h3>
                        <button type="button" @click="showConfirmModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-3 text-xs md:text-sm text-slate-700 dark:text-slate-300">
                        <p>You are about to authorize and dispense <strong class="font-extrabold text-slate-900 dark:text-white" x-text="selected.length"></strong> selected blood requisition(s).</p>
                        <p class="text-xs text-slate-600 dark:text-slate-400 italic">Server transaction will execute FEFO allocation for each selected requisition. Final allocated units are determined at transaction execution.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showConfirmModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting" 
                                @click="isSubmitting = true" 
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs shadow-md transition inline-flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50">
                            <template x-if="isSubmitting">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </template>
                            <span x-text="isSubmitting ? 'Processing Batch...' : 'Confirm Bulk Allocation'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-center">
                                <input type="checkbox" @click="selected = $el.checked ? [{{ $triagedRequests->where('has_compatible_stock', true)->pluck('id')->implode(',') }}] : []" class="rounded border-slate-300 dark:border-slate-700 text-rose-600 focus:ring-rose-500">
                            </th>
                            <th class="px-4 py-3">Triage Priority</th>
                            <th class="px-4 py-3">Req #</th>
                            <th class="px-4 py-3">Hospital & Location</th>
                            <th class="px-4 py-3">Patient Name</th>
                            <th class="px-4 py-3">Requested Blood</th>
                            <th class="px-4 py-3">FEFO Recommendation</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                        @forelse($triagedRequests as $req)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 {{ $req->urgency_level === 'emergency' ? 'bg-rose-50/30 dark:bg-rose-950/20' : '' }} transition">
                                <td class="px-4 py-3 text-center">
                                    @if($req->has_compatible_stock)
                                        <input type="checkbox" name="request_ids[]" value="{{ $req->id }}" x-model="selected" class="rounded border-slate-300 dark:border-slate-700 text-rose-600 focus:ring-rose-500">
                                    @else
                                        <span class="text-slate-400" title="Approval disabled: No compatible stock in vault">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$req->urgency_level" />
                                </td>
                                <td class="px-4 py-3 font-extrabold text-slate-900 dark:text-white">#REQ-{{ $req->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900 dark:text-slate-100 text-xs">{{ $req->hospitalEntity->name ?? $req->hospital }}</div>
                                    <div class="text-[11px] text-emerald-700 dark:text-emerald-400 font-semibold flex items-center gap-1 mt-0.5">
                                        <span>📍</span> {{ $req->ward_name ?? ($req->patient->ward_name ?? 'General Ward') }}
                                        @if($req->room_number || $req->patient->room_number) | R: {{ $req->room_number ?? $req->patient->room_number }} @endif
                                        @if($req->bed_number || $req->patient->bed_number) | B: {{ $req->bed_number ?? $req->patient->bed_number }} @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ $req->patient->name ?? $req->patient_name }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Attendant: {{ $req->attendant_name ?? 'Pending' }}</div>
                                </td>
                                <td class="px-4 py-3 font-bold">
                                    <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-black rounded-lg shadow-sm">
                                        {{ $req->blood_group }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($req->recommended_compat)
                                        @php $rec = $req->recommended_compat; @endphp
                                        <div class="p-2 bg-slate-50 dark:bg-[#070d1a] border border-slate-200 dark:border-slate-800 rounded-xl text-xs space-y-0.5">
                                            <p class="font-bold text-slate-900 dark:text-white flex items-center gap-1">
                                                <span>{{ ucfirst($rec['match_type']) }} Match:</span> {{ $rec['group'] }}
                                            </p>
                                            <p class="font-mono text-slate-600 dark:text-slate-400 text-[11px]">Candidate Bag #{{ $rec['unit']->unit_number }} (Storage: {{ $rec['unit']->storage_location ?? 'Shelf A' }})</p>
                                        </div>
                                    @else
                                        <div class="p-2 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-xs text-rose-800 dark:text-rose-300">
                                            <p class="font-bold">Stock Depleted (0 Units Available)</p>
                                            <p class="text-[11px] text-rose-600 dark:text-rose-400">Requires Donor Priority Cascade</p>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($req->has_compatible_stock)
                                        <x-status-badge status="available" label="Ready to Issue" />
                                    @else
                                        <x-status-badge status="emergency" label="Shortage Alert" />
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-slate-500 italic">No pending requisitions in queue right now. All requests fulfilled.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>
@endsection
