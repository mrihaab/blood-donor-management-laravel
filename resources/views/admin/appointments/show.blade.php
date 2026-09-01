@extends('layouts.admin')

@section('title', 'Clinical Donation Workflow')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <x-breadcrumbs :items="[
        ['label' => 'Appointments', 'url' => route('admin.appointments.index')],
        ['label' => 'Appointment #' . $appointment->id]
    ]" />

    <!-- Title & Status Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $appointment->donor->user->name ?? 'Donor #' . $appointment->donor_id }}</h1>
                <span class="rounded-md bg-red-700 px-2.5 py-0.5 text-xs font-bold text-white">
                    {{ $appointment->donor->bloodGroup->name ?? 'N/A' }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Appointment #{{ $appointment->id }} &bull; Scheduled for {{ $appointment->appointment_date }} ({{ $appointment->appointment_time ?? '10:00 AM' }})</p>
        </div>
        <div class="flex items-center gap-2">
            <x-status-badge :status="$appointment->status" />
            <a href="{{ route('admin.appointments.index') }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                &larr; Back to List
            </a>
        </div>
    </div>

    <!-- 3-Step Clinical Stepper Header -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h2 class="text-base font-bold text-slate-900">🏥 Clinical Donation Stepper & Intake Process</h2>
            <span class="text-xs font-medium text-slate-500">WHO & ISBT-128 Medical Protocol</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-slate-500">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-900 text-white text-[10px]">1</span>
                    <span>Donor Screening</span>
                </div>
                <p class="text-xs text-slate-600 mt-2">Vitals check (Hemoglobin &ge; 12.5 g/dL, Weight &ge; 50kg).</p>
            </div>

            <div class="p-4 rounded-xl bg-red-50 border border-red-200">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-red-700">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-white text-[10px]">2</span>
                    <span>Donation Intake</span>
                </div>
                <p class="text-xs text-red-800 mt-2">Auto-prefilled donor bag collection & shelf tagging.</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div class="flex items-center gap-2 text-xs font-bold uppercase text-slate-500">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-900 text-white text-[10px]">3</span>
                    <span>Central Inventory</span>
                </div>
                <p class="text-xs text-slate-600 mt-2">Auto-ingest bag serial, update stock (+1) & 56-day cooldown.</p>
            </div>
        </div>

        @if($appointment->status === 'completed')
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-1">
                <div class="flex items-center gap-2 font-bold text-sm">
                    <span>✓ Donation Intake Successfully Processed</span>
                </div>
                <p class="text-xs text-emerald-700">This appointment has been marked as Completed and the blood unit bag was intaken into Central Inventory stock.</p>
            </div>
        @else
            <!-- 1-CLICK DONATION INTAKE FORM -->
            <form method="POST" action="{{ route('admin.appointments.intake', $appointment->id) }}" class="space-y-6 pt-4 border-t border-slate-100">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Donor Name (Auto-Filled)</label>
                        <input type="text" value="{{ $appointment->donor->user->name ?? 'Donor' }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-700 font-semibold cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Blood Group (Auto-Filled)</label>
                        <input type="text" value="{{ $appointment->donor->bloodGroup->name ?? 'N/A' }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-red-700 font-bold cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Blood Component Type</label>
                        <select name="component_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                            @foreach($components as $component)
                                <option value="{{ $component->id }}" {{ $component->name === 'Whole Blood' ? 'selected' : '' }}>
                                    {{ $component->name }} ({{ $component->code }}) &bull; Shelf Life: {{ $component->shelf_life_days }} Days
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Collected Volume (mL)</label>
                        <input type="number" name="volume_ml" value="450" min="200" max="600" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Storage Location Bay / Shelf</label>
                        <input type="text" name="storage_location" value="Central Blood Bank Storage Room A - Refrigerator #2" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <span class="text-xs text-slate-500">Submitting will ingest +1 unit bag to inventory with 🟢 Donor Badge & auto-update donor 56-day cooldown.</span>
                    <button type="submit" class="rounded-xl bg-red-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-red-700 transition flex items-center gap-2">
                        <span>🩸 Complete Intake & Ingest Bag</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
