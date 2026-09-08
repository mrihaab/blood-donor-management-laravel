@extends('layouts.donor')

@section('title', 'Donation History')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 transition-colors">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Donation History Ledger</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Verified records of your past blood donations and collection entries.</p>
        </div>
        <a href="{{ route('donor.appointments.create') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-sm transition inline-flex items-center gap-1.5 self-start md:self-auto">
            <span>+ Schedule New Donation</span>
        </a>
    </div>

    <!-- History Data Table Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm transition-colors space-y-4">
        <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">Recorded Blood Donations</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-extrabold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="col" class="px-4 py-3">Donation Date</th>
                        <th scope="col" class="px-4 py-3">Blood Group</th>
                        <th scope="col" class="px-4 py-3">Quantity (Units)</th>
                        <th scope="col" class="px-4 py-3">Collection Center</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($donations as $donation)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3.5 font-bold font-mono text-slate-900 dark:text-white">
                                {{ $donation->donation_date ?? (is_object($donation->created_at) ? $donation->created_at->format('Y-m-d') : $donation->created_at) }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 text-xs font-extrabold rounded-md">
                                    {{ $donation->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white font-mono">
                                {{ $donation->quantity }} Bag(s)
                            </td>
                            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300">
                                {{ $donation->collection_center ?? 'Main Blood Bank Center' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400 italic">
                                <div class="flex flex-col items-center space-y-2">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>No prior donation records found in the system.</span>
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
