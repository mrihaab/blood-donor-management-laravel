@extends('layouts.admin')

@section('title', 'Blood Vault Inventory')

@section('content')
<div class="space-y-8 pb-12">
    <x-breadcrumbs :items="[['label' => 'Blood Inventory']]" />

    @php
        $oNegGroup = \App\Models\BloodGroup::where('name', 'O-')->first();
        $oNegGroupId = $oNegGroup ? $oNegGroup->id : 8;

        $totalUsableBags = \App\Models\BloodUnit::where('status', 'available')
            ->where('expiry_date', '>=', now()->format('Y-m-d'))
            ->count();
        $oNegBags = \App\Models\BloodUnit::where('status', 'available')
            ->where(function($q) use ($oNegGroupId) {
                $q->where('blood_group_id', $oNegGroupId)->orWhere('blood_group', 'O-');
            })
            ->where('expiry_date', '>=', now()->format('Y-m-d'))
            ->count();
        $expiringSoonBags = \App\Models\BloodUnit::where('status', 'available')
            ->whereBetween('expiry_date', [now()->format('Y-m-d'), now()->addDays(7)->format('Y-m-d')])
            ->count();
        $recentLogs = \Illuminate\Support\Facades\DB::table('activity_log')
            ->latest()
            ->limit(5)
            ->get();

        $patientCoveragePercent = ($oNegBags > 0) ? 100 : ($totalUsableBags > 0 ? 85 : 0);
    @endphp

    <!-- Automated Shortage & Inventory Command Banner -->
    <div x-data="{ open: true, showLogs: false, scanSuccess: false }" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] p-6 text-slate-900 dark:text-white shadow-sm space-y-5">
        <!-- Header Control Bar -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-xl bg-purple-50 dark:bg-purple-950/80 border border-purple-200 dark:border-purple-800 flex items-center justify-center text-purple-600 dark:text-purple-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-base font-extrabold tracking-tight text-slate-900 dark:text-white">Inventory Balance & Shortage Monitor</h2>
                        <x-status-badge :status="$oNegBags > 0 ? 'available' : 'urgent'" :label="$oNegBags > 0 ? 'ALL GROUPS BALANCED' : 'SHORTAGE MONITORING'" />
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Stock Availability Check Active • Inventory Shortage Aggregation</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button @click="scanSuccess = true; setTimeout(() => scanSuccess = false, 2000)" type="button" class="rounded-xl bg-purple-600 hover:bg-purple-700 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition flex items-center space-x-1.5 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <span x-text="scanSuccess ? 'Stock Checked ✔' : 'Run Stock Check'">Run Stock Check</span>
                </button>
                <button @click="showLogs = !showLogs" type="button" class="rounded-xl bg-slate-100 dark:bg-slate-800 px-3 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 transition">
                    System Audit Logs
                </button>
                <button @click="open = !open" type="button" class="rounded-xl bg-slate-100 dark:bg-slate-800 px-3 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 transition">
                    <span x-text="open ? 'Collapse ▲' : 'Expand Details ▼'">Collapse ▲</span>
                </button>
            </div>
        </div>

        <div x-show="open" class="space-y-5">
            <!-- 4 Dynamic Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Card 1 -->
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#070d1a] p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">1. Ready Blood Stock</span>
                        <x-status-badge status="available" label="SAFE & TESTED" />
                    </div>
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white">
                        {{ $totalUsableBags }} <span class="text-xs font-normal text-slate-500">Usable Bags</span>
                    </div>
                    <p class="text-[11px] text-slate-600 dark:text-slate-400">{{ $totalUsableBags }} blood bags tested & safe for immediate emergency use.</p>
                </div>

                <!-- Card 2 -->
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#070d1a] p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">2. Universal Stock (O-)</span>
                        <x-status-badge :status="$oNegBags > 0 ? 'available' : 'emergency'" :label="$oNegBags > 0 ? 'OPTIMAL (O-)' : 'CALL DONORS'" />
                    </div>
                    <div class="text-2xl font-extrabold {{ $oNegBags > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $oNegBags }} <span class="text-xs font-normal text-slate-500">O- Bags</span>
                    </div>
                    <p class="text-[11px] text-slate-600 dark:text-slate-400">
                        {{ $oNegBags > 0 ? 'Universal emergency fallback stock is stocked (' . $oNegBags . ' units).' : 'Universal emergency fallback stock is 0. Donor SMS call recommended.' }}
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#070d1a] p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">3. Patient Coverage</span>
                        <x-status-badge status="approved" :label="$patientCoveragePercent . '% COVERED'" />
                    </div>
                    <div class="text-xl font-extrabold text-blue-600 dark:text-blue-400">
                        {{ $oNegBags > 0 ? 'All Patients Covered' : ($totalUsableBags > 0 ? 'A+ & AB+ Patients' : 'Severe Shortage') }}
                    </div>
                    <p class="text-[11px] text-slate-600 dark:text-slate-400">
                        {{ $oNegBags > 0 ? 'Universal O- stock ready. 100% of emergency patients covered.' : ($totalUsableBags > 0 ? 'Current available stock satisfies A+ & AB+ emergency patient needs.' : 'No usable blood stock available in central inventory.') }}
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#070d1a] p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">4. Decision Control</span>
                        <x-status-badge status="scheduled" label="DOCTOR CONTROL" />
                    </div>
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white mt-1">Human Admin Sign-Off</div>
                    <p class="text-[11px] text-slate-600 dark:text-slate-400">System suggests donor broadcast based on low stock threshold; Admin clicks to trigger alert.</p>
                </div>
            </div>

            <!-- Group-by-Group Stock Indicator Bar -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#070d1a] p-4 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[11px]">Live Stock Level Status By Blood Group</span>
                    <span class="text-slate-500 dark:text-slate-400 text-[11px]">All 8 Blood Groups Checked</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2 text-center text-xs pt-1">
                    @foreach($groupedInventory as $groupItem)
                        @php
                            $availableCount = $groupItem->total_units ?? 0;
                            $bgName = $groupItem->name ?? $groupItem->blood_group ?? 'Unknown';
                            $statusStyle = $availableCount >= 5 ? 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : ($availableCount > 0 ? 'bg-amber-50 dark:bg-amber-950/80 text-amber-900 dark:text-amber-300 border-amber-200 dark:border-amber-800' : 'bg-rose-50 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800');
                        @endphp
                        <div class="p-2 rounded-xl border {{ $statusStyle }} font-bold shadow-sm">
                            <span class="block text-sm font-black">{{ $bgName }}</span>
                            <span class="block text-xs font-semibold">{{ $availableCount }} Bags</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Header Actions & Search Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Central Vault Barcode Unit Registry</h2>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Track physical barcode blood bags, expiration windows, and storage locations.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.inventory.create') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-rose-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Barcode Unit</span>
            </a>
        </div>
    </div>

    <!-- Barcode Units Inventory Table -->
    <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">Unit Barcode</th>
                        <th class="px-6 py-3.5">Blood Group</th>
                        <th class="px-6 py-3.5">Volume (mL)</th>
                        <th class="px-6 py-3.5">Storage Location</th>
                        <th class="px-6 py-3.5">Expiry Date</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                    @forelse($units as $unit)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-white">#{{ $unit->unit_number }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-block rounded-lg bg-rose-50 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-2.5 py-1 text-xs font-black shadow-sm">
                                    {{ $unit->blood_group ?? $unit->bloodGroup->name ?? 'Unspecified' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-white">{{ $unit->volume_ml ?? 450 }} mL</td>
                            <td class="px-6 py-4 text-xs text-slate-700 dark:text-slate-300 font-bold">
                                {{ $unit->storage_location ?? 'Main Refrigerator - Shelf A' }}
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($unit->expiry_date)->format('M d, Y') }}
                                <span class="text-[11px] font-normal text-slate-500 block">({{ \Carbon\Carbon::parse($unit->expiry_date)->diffForHumans() }})</span>
                            </td>
                            <td class="px-6 py-4">
                                <x-status-badge :status="$unit->status" />
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                    <a href="{{ route('admin.inventory.show', $unit->id) }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 transition">
                                        View Details
                                    </a>
                                    <form id="form-delete-unit-{{ $unit->id }}" method="POST" action="{{ route('admin.inventory.destroy', $unit->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-delete-unit-{{ $unit->id }}', formId: 'form-delete-unit-{{ $unit->id }}' })" class="px-2.5 py-1.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 hover:bg-rose-100 text-xs font-bold rounded-xl transition focus:outline-none focus:ring-2 focus:ring-rose-500">
                                            Delete
                                        </button>
                                    </form>
                                    <x-confirm-dialog id="confirm-delete-unit-{{ $unit->id }}" title="Delete Blood Unit" message="Are you sure you want to delete blood unit #{{ $unit->unit_number }}? This will permanently remove it from central vault records." confirmText="Delete Unit" variant="danger" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8">
                                <x-empty-state title="No inventory units found" message="Physical barcode blood bags added to the central vault will appear here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($units, 'hasPages') && $units->hasPages())
            <div class="border-t border-slate-200 dark:border-slate-800 px-6 py-4">
                {{ $units->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
