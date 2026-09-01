@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Direct Blood Stock Intake</h1>
            <p class="text-sm text-slate-500">Ingest physical blood unit bags directly into central storage inventory.</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Back to Inventory</a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl bg-red-50 p-4 border border-red-200 text-sm text-red-800 space-y-1">
            <span class="font-bold">Please correct the following errors:</span>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.inventory.store') }}" class="space-y-6">
        @csrf

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span>🩸</span> Blood Unit Bag Details
            </h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Blood Group *</label>
                    <select name="blood_group_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                        <option value="">Select Blood Group</option>
                        @foreach($bloodGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Blood Component</label>
                    <select name="blood_component_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                        <option value="">Whole Blood (Standard)</option>
                        @foreach($components as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Number of Bags (Units) *</label>
                    <input type="number" name="units" min="1" max="50" value="1" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Shelf Life (Expiration Days) *</label>
                    <input type="number" name="expiration_days" min="1" max="365" value="42" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                    <span class="text-xs text-slate-400">Standard CPDA-1 Whole Blood shelf life is 35-42 days.</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.inventory.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
            <button type="submit" class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-red-700 shadow-md transition">
                + Add Units to Storage
            </button>
        </div>
    </form>
</div>
@endsection
