@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <x-breadcrumbs :items="[['label' => 'Blood Inventory']]" />

    <!-- Page Title & Header Actions -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Central Blood Inventory Stock</h1>
            <p class="text-sm text-slate-500">Real-time stock management, ISBT-128 barcode unit tracking, and donor intake origins.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.inventory.create') }}" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                + Direct Stock Intake
            </a>
            <a href="{{ route('admin.donations.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 transition">
                + Intake Donation Unit
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            <span class="font-bold">Success!</span> {{ session('success') }}
        </div>
    @endif

    <!-- Interactive Top Blood Group Quick Stock Summary Cards -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
        @foreach($groupedInventory as $groupItem)
            <a href="{{ route('admin.inventory.index', ['tab' => 'detailed', 'blood_group_id' => $groupItem['blood_group_id']]) }}" 
               class="group relative rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:border-red-500 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-red-100 text-xs font-bold text-red-700 group-hover:bg-red-600 group-hover:text-white transition">
                        {{ $groupItem['blood_group'] }}
                    </span>
                    @if($groupItem['is_low_stock'])
                        <span class="h-2 w-2 rounded-full bg-amber-500 ring-4 ring-amber-100" title="Low Stock Alert"></span>
                    @else
                        <span class="h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
                    @endif
                </div>
                <div class="mt-3">
                    <span class="text-xl font-bold text-slate-900">{{ $groupItem['units_available'] }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 block">Units Available</span>
                </div>
                @if($groupItem['expiring_soon'] > 0)
                    <span class="mt-1 text-[10px] font-semibold text-amber-700 block">⚠️ {{ $groupItem['expiring_soon'] }} Expiring Soon</span>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Navigation Tabs Header -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-6 text-sm font-semibold">
            <a href="{{ route('admin.inventory.index', ['tab' => 'grouped']) }}" 
               class="pb-3 border-b-2 transition flex items-center gap-2 {{ $currentTab === 'grouped' ? 'border-red-600 text-red-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <span>📊 Grouped Stock Summary</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600 font-mono">{{ count($groupedInventory) }} Groups</span>
            </a>
            <a href="{{ route('admin.inventory.index', ['tab' => 'detailed', 'source' => $sourceFilter, 'per_page' => $perPage]) }}" 
               class="pb-3 border-b-2 transition flex items-center gap-2 {{ $currentTab === 'detailed' ? 'border-red-600 text-red-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <span>🏷️ Barcode Units Tracking</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600 font-mono">{{ $bloodUnits->total() }} Bags</span>
            </a>
        </nav>
    </div>

    @if($currentTab === 'grouped')
        <!-- TAB 1: GROUPED STOCK SUMMARY VIEW -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Stock Availability by Blood Group</h3>
                <span class="text-xs text-slate-500">Aggregated real-time stock and intake breakdown</span>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-semibold text-slate-600">
                    <tr>
                        <th class="px-6 py-3.5">Blood Group</th>
                        <th class="px-6 py-3.5">Stock Status</th>
                        <th class="px-6 py-3.5">Available Units</th>
                        <th class="px-6 py-3.5">Origin Breakdown</th>
                        <th class="px-6 py-3.5">Expiring Soon (7 Days)</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium text-slate-800">
                    @foreach($groupedInventory as $group)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2">
                                    <span class="inline-block rounded-md bg-red-700 px-2.5 py-1 text-xs font-bold text-white">
                                        {{ $group['blood_group'] }}
                                    </span>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($group['is_low_stock'])
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                                        ⚠️ Low Stock Threshold
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                        ✓ Optimal Stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 text-base">
                                {{ $group['units_available'] }} Bags
                            </td>
                            <td class="px-6 py-4 text-xs space-y-1">
                                <div>
                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>
                                    <span class="font-bold text-emerald-900">{{ $group['donor_intake_count'] }}</span>
                                    <span class="text-slate-500">from Donor Intake</span>
                                </div>
                                <div>
                                    <span class="inline-block w-2 h-2 rounded-full bg-purple-500 mr-1"></span>
                                    <span class="font-bold text-purple-900">{{ $group['direct_intake_count'] }}</span>
                                    <span class="text-slate-500">from Direct Ingestion</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($group['expiring_soon'] > 0)
                                    <span class="font-bold text-amber-600">{{ $group['expiring_soon'] }} Bag(s)</span>
                                @else
                                    <span class="text-slate-400">0 Bags</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.inventory.index', ['tab' => 'detailed', 'blood_group_id' => $group['blood_group_id']]) }}" 
                                   class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 transition">
                                    View {{ $group['units_available'] }} Bags &rarr;
                                </a>
                                <a href="{{ route('admin.inventory.create') }}" 
                                   class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 transition">
                                    + Add Stock
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <!-- TAB 2: DETAILED BARCODE UNITS TRACKING VIEW -->
        <div class="space-y-4">
            <!-- Filters Bar -->
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('admin.inventory.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-5 items-end">
                    <input type="hidden" name="tab" value="detailed">

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Search Barcode Serial</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="BAG-AP-XXXX..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Blood Group</label>
                        <select name="blood_group_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                            <option value="">All Blood Groups</option>
                            @foreach($bloodGroups as $group)
                                <option value="{{ $group->id }}" {{ request('blood_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Intake Origin / Source</label>
                        <select name="source" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                            <option value="all" {{ $sourceFilter === 'all' ? 'selected' : '' }}>All Sources</option>
                            <option value="donor" {{ $sourceFilter === 'donor' ? 'selected' : '' }}>🟢 Donor Donations Only</option>
                            <option value="direct" {{ $sourceFilter === 'direct' ? 'selected' : '' }}>🟣 Direct Admin Intake Only</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Bags Per Page</label>
                        <select name="per_page" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 per page</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 per page</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 per page</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 per page</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 rounded-lg bg-slate-900 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">Filter</button>
                        <a href="{{ route('admin.inventory.index', ['tab' => 'detailed']) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Detailed Bags Data Table -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 font-semibold text-slate-600">
                        <tr>
                            <th class="px-6 py-3.5">Unit Serial #</th>
                            <th class="px-6 py-3.5">Blood Group</th>
                            <th class="px-6 py-3.5">Component</th>
                            <th class="px-6 py-3.5">Origin / Source</th>
                            <th class="px-6 py-3.5">Collection</th>
                            <th class="px-6 py-3.5">Expiry Date</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium text-slate-800">
                        @forelse($bloodUnits as $unit)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('admin.inventory.show', $unit->id) }}" class="hover:underline hover:text-red-600">
                                        {{ $unit->unit_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block rounded-md bg-red-700 px-2.5 py-0.5 text-xs font-bold text-white">
                                        {{ $unit->bloodGroup->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                    {{ $unit->component->name ?? 'Whole Blood' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($unit->donor_id && $unit->donor)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-800 border border-emerald-200">
                                            <span>🟢 Donor:</span>
                                            <a href="{{ route('admin.donors.show', $unit->donor_id) }}" class="underline font-bold hover:text-emerald-950">
                                                {{ $unit->donor->user->name ?? "Donor #{$unit->donor_id}" }}
                                            </a>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-md bg-purple-50 px-2 py-1 text-xs font-semibold text-purple-800 border border-purple-200">
                                            <span>🟣 Direct Intake</span>
                                            <span class="text-[10px] text-purple-600">(Admin Stock)</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-mono">{{ $unit->collection_date }}</td>
                                <td class="px-6 py-4 text-xs font-mono font-semibold text-slate-900">{{ $unit->expiry_date }}</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :status="$unit->status" />
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.inventory.show', $unit->id) }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                        Audit Trail
                                    </a>
                                    <form method="POST" action="{{ route('admin.inventory.destroy', $unit->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to discard/delete blood unit bag {{ $unit->unit_number }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 hover:bg-red-100 transition">
                                            Discard / Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8">
                                    <x-empty-state title="No blood unit bags found" description="Physical blood unit bags matching your filter criteria will appear here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($bloodUnits->hasPages())
                    <div class="border-t border-slate-200 px-6 py-4">
                        {{ $bloodUnits->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
