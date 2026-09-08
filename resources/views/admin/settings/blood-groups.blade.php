@extends('layouts.admin')

@section('title', 'Manage Blood Groups')
@section('page_title', 'Inventory Threshold Config')

@section('content')
<div class="space-y-6 max-w-4xl pb-12">
    <!-- Sub-Navigation Header Tabs -->
    <div class="flex space-x-4 border-b border-slate-200 dark:border-slate-800 pb-3">
        <a href="{{ route('admin.settings.index') }}" class="text-xs md:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition pb-2">General Settings</a>
        <a href="{{ route('admin.settings.blood_groups') }}" class="text-xs md:text-sm font-extrabold text-rose-600 dark:text-rose-400 border-b-2 border-rose-600 dark:border-rose-400 pb-2">Blood Group Thresholds</a>
        <a href="{{ route('admin.settings.cities') }}" class="text-xs md:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition pb-2">City Coverage</a>
    </div>

    <!-- Main Card Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 md:p-8 space-y-6">
        <div>
            <h3 class="font-extrabold text-slate-900 dark:text-white text-lg tracking-tight">ABO/Rh Inventory Threshold Matrix</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Configured blood group types and reserve balance thresholds used for system shortage alerts.</p>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427]">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">Blood Group</th>
                        <th class="px-6 py-3.5">ABO / Rh Type</th>
                        <th class="px-6 py-3.5">Operational Description</th>
                        <th class="px-6 py-3.5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                    @foreach($bloodGroups as $group)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-6 py-4 font-mono font-black text-rose-600 dark:text-rose-400 text-base">
                                {{ $group->name }}
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-900 dark:text-white">
                                {{ str_contains($group->name, '-') ? 'Rh Negative (Universal Donor/Recipient Pool)' : 'Rh Positive' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400">
                                {{ $group->description ?? 'Standard ABO/Rh Blood Group Profile' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-status-badge status="available" label="Active Profile" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
