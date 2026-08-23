@extends('layouts.admin')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">&larr; Back to Barcode Inventory</a>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Blood Unit Details: <span class="font-mono text-red-700">{{ $unit->unit_number }}</span></h1>
        </div>
        <x-status-badge :status="$unit->status" />
    </div>

    <!-- Unit Core Metadata Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Medical Specs</span>
            <div>
                <p class="text-xs text-slate-500">Blood Group</p>
                <p class="text-lg font-bold text-slate-900">{{ $unit->bloodGroup->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Component</p>
                <p class="text-sm font-semibold text-slate-800">{{ $unit->component->name ?? 'Packed Red Blood Cells' }} ({{ $unit->component->code ?? 'PRBC' }})</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Volume</p>
                <p class="text-sm font-semibold text-slate-800">{{ $unit->volume_ml }} mL</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Preservation & Storage</span>
            <div>
                <p class="text-xs text-slate-500">Collection Date</p>
                <p class="text-sm font-semibold text-slate-800">{{ $unit->collection_date }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Expiry Date</p>
                <p class="text-sm font-semibold text-slate-800">{{ $unit->expiry_date }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Storage Location</p>
                <p class="text-sm font-semibold text-slate-800">{{ $unit->storage_location ?? 'Bay 1 Refrigerator' }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Chain of Custody</span>
            <div>
                <p class="text-xs text-slate-500">Donor Reference</p>
                <p class="text-sm font-semibold text-slate-800">Donor #{{ $unit->donor_id }} ({{ $unit->donor->user->name ?? 'Anonymous' }})</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Donation Requisition</p>
                <p class="text-sm font-semibold text-slate-800">Donation #{{ $unit->donation_id }}</p>
            </div>
        </div>
    </div>

    <!-- Auditable Inventory Transaction Timeline -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-4">Auditable Inventory Transactions Log</h2>

        <div class="mt-6 space-y-6">
            @forelse($unit->inventoryTransactions as $tx)
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="h-3 w-3 rounded-full bg-red-600 ring-4 ring-red-100"></div>
                        <div class="h-full w-0.5 bg-slate-200 my-1"></div>
                    </div>
                    <div class="flex-1 pb-4">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-900 uppercase font-mono">{{ $tx->transaction_type }}</span>
                            <span class="text-xs text-slate-400 font-mono">{{ $tx->created_at->format('M d, Y H:i:s') }}</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-1">{{ $tx->reason }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Performed by: {{ $tx->user->name ?? 'System' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-4">No transactions recorded for this unit.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
