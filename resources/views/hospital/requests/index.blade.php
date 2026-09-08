@extends('layouts.hospital')

@section('title', 'Blood Requisitions Stream')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('hospital.dashboard')],
        ['label' => 'Blood Requisitions Stream']
    ]" />

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Blood Component Requisitions Log</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Track bedside requisitions, cross-matching status, and fulfillment delivery timelines.</p>
        </div>

        <a href="{{ route('hospital.requests.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-lg shadow-blue-600/30 flex items-center gap-2 self-start sm:self-auto">
            <span>+ New Blood Requisition</span>
        </a>
    </div>

    <!-- Requisition Log Table Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm transition-colors space-y-4">
        
        <!-- Filter Bar -->
        <form method="GET" action="{{ route('hospital.requests.index') }}" x-data="{ isSubmitting: false }" @change="isSubmitting = true; $el.submit()" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <label for="status-filter" class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Filter Status:</label>
                <select id="status-filter" name="status" class="px-4 py-2 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending Review</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>🧪 Approved / Allocated</option>
                    <option value="dispensed" {{ request('status') === 'dispensed' ? 'selected' : '' }}>✅ Dispensed & Transfused</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                </select>
            </div>
            @if(request('status'))
                <a href="{{ route('hospital.requests.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">Clear Filter</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-extrabold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 rounded-l-xl">Req #</th>
                        <th scope="col" class="px-4 py-3.5">Patient Details</th>
                        <th scope="col" class="px-4 py-3.5">Blood Group</th>
                        <th scope="col" class="px-4 py-3.5">Units Required</th>
                        <th scope="col" class="px-4 py-3.5">Status</th>
                        <th scope="col" class="px-4 py-3.5">Timestamp</th>
                        <th scope="col" class="px-4 py-3.5 text-right rounded-r-xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-4 font-black text-blue-600 dark:text-blue-400">#REQ-{{ $req->id }}</td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $req->patient->name ?? $req->patient_name }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $req->ward_name ?? 'General Ward' }}
                                    @if($req->bed_number)
                                        • Bed {{ $req->bed_number }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-rose-50 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80 shadow-xs">
                                    🩸 {{ $req->blood_group }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-extrabold text-slate-800 dark:text-slate-200">
                                {{ $req->units_needed }} Bag(s)
                            </td>
                            <td class="px-4 py-4">
                                <x-status-badge :status="$req->status" />
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                {{ $req->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('hospital.requests.show', $req->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 dark:bg-blue-600/20 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white font-bold rounded-lg text-xs border border-blue-200 dark:border-blue-500/30 transition">
                                        <span>Details</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <x-empty-state 
                                    title="No Blood Requisitions Logged" 
                                    description="No blood component requests match your filter selection."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
