@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <x-breadcrumbs :items="[['label' => 'Blood Inventory']]" />

    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Physical Blood Units & Barcode Inventory</h1>
            <p class="text-sm text-slate-500">Unit-level bag tracking, component shelf-life, and storage bay management.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.inventory.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                <span>🩸</span> + Direct Stock Intake
            </a>
            <a href="{{ route('admin.donations.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-900 transition">
                <span>👤</span> + Intake Donation Unit
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            <span class="font-bold">Success!</span> {{ session('success') }}
        </div>
    @endif

    <!-- Search & Filter Controls -->
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label for="search" class="block text-xs font-semibold text-slate-600 mb-1">Search Barcode Serial</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="UNIT-2026-..." class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-red-500 focus:outline-none">
            </div>

            <div>
                <label for="blood_group_id" class="block text-xs font-semibold text-slate-600 mb-1">Blood Group</label>
                <select name="blood_group_id" id="blood_group_id" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-red-500 focus:outline-none">
                    <option value="">All Blood Groups</option>
                    @foreach($bloodGroups as $group)
                        <option value="{{ $group->id }}" {{ request('blood_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-slate-600 mb-1">Unit Status</label>
                <select name="status" id="status" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-red-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                    <option value="allocated" {{ request('status') == 'allocated' ? 'selected' : '' }}>Allocated</option>
                    <option value="dispensed" {{ request('status') == 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="discarded" {{ request('status') == 'discarded' ? 'selected' : '' }}>Discarded</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">
                    Filter Units
                </button>
                <a href="{{ route('admin.inventory.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Blood Units Data Table -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 font-semibold text-slate-600">
                <tr>
                    <th class="px-6 py-3.5">Unit Barcode Serial</th>
                    <th class="px-6 py-3.5">Blood Group</th>
                    <th class="px-6 py-3.5">Component Type</th>
                    <th class="px-6 py-3.5">Collection Date</th>
                    <th class="px-6 py-3.5">Expiry Date</th>
                    <th class="px-6 py-3.5">Storage Bay</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-medium text-slate-800">
                @forelse($bloodUnits as $unit)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 font-mono font-bold text-slate-900">
                            {{ $unit->unit_number }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block rounded-md bg-red-700 px-2 py-0.5 text-xs font-bold text-white">
                                {{ $unit->bloodGroup->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-slate-700">{{ $unit->component->name ?? 'Packed Red Cells' }}</span>
                            <span class="text-xs text-slate-400 block font-mono">({{ $unit->component->code ?? 'PRBC' }})</span>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $unit->collection_date }}</td>
                        <td class="px-6 py-4">
                            @php
                                $isExpired = \Carbon\Carbon::parse($unit->expiry_date)->isPast();
                            @endphp
                            <span class="{{ $isExpired ? 'font-bold text-rose-600' : 'text-slate-700' }}">
                                {{ $unit->expiry_date }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $unit->storage_location ?? 'Bay 1' }}</td>
                        <td class="px-6 py-4">
                            <x-status-badge :status="$unit->status" />
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.inventory.show', $unit) }}" class="inline-flex items-center rounded-md border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                Audit Trail
                            </a>
                            <form method="POST" action="{{ route('admin.inventory.destroy', $unit) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to discard/delete blood unit {{ $unit->unit_number }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 hover:bg-red-100 transition">
                                    Discard / Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8">
                            <x-empty-state title="No blood units match filters" description="Try adjusting search terms or intake new donation units." />
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
@endsection
