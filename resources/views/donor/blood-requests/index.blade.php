@extends('layouts.donor')

@section('title', 'My Blood Requests')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Community Blood Requests</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">View status of family blood requisitions or submit a request for a patient in need.</p>
        </div>
        <a href="{{ route('donor.blood_requests.create') }}" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs md:text-sm shadow-sm transition inline-flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ Submit Blood Request</span>
        </a>
    </div>

    <!-- Requests Table Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm transition-colors space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">Submitted Requests</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-extrabold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="col" class="px-4 py-3">Patient Name</th>
                        <th scope="col" class="px-4 py-3">Blood Group</th>
                        <th scope="col" class="px-4 py-3">Units Needed</th>
                        <th scope="col" class="px-4 py-3">Hospital Location</th>
                        <th scope="col" class="px-4 py-3">Urgency</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse(($requests ?? $bloodRequests ?? []) as $req)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">
                                {{ $req->patient_name ?? $req->requester_name ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 text-xs font-extrabold rounded-md">
                                    {{ $req->blood_group ?? $req->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-bold font-mono text-slate-900 dark:text-white">
                                {{ $req->units_needed ?? $req->units ?? 1 }} Bag(s)
                            </td>
                            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300">
                                {{ $req->hospital_name ?? $req->hospital ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 text-xs font-extrabold rounded-md uppercase tracking-wider {{ ($req->urgency ?? 'normal') === 'emergency' || ($req->urgency ?? 'normal') === 'urgent' ? 'bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                    {{ ucfirst($req->urgency ?? 'normal') }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <x-status-badge :status="$req->status" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400 italic">
                                <div class="flex flex-col items-center space-y-2">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <span>No blood requests submitted.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
