@extends('layouts.admin')

@section('title', 'Add Inventory Unit')
@section('page_title', 'Direct Blood Stock Intake')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Direct Blood Stock Intake</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Ingest physical blood unit bags directly into central storage inventory.</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 transition">
            &larr; Back to Inventory
        </a>
    </div>

    <!-- Validation Error Summary -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/90 border-l-4 border-rose-500 text-rose-800 dark:text-rose-200 shadow-md space-y-2" role="alert">
            <div class="flex items-center gap-2 font-bold text-sm">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Please correct the following errors:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.inventory.store') }}" class="space-y-6" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
        @csrf

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] p-6 md:p-8 shadow-sm space-y-6 transition-colors relative">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Blood Unit Bag Intake Details</span>
                </h2>
                <x-status-badge status="approved" label="USB Barcode Scanner Ready" />
            </div>

            <!-- Handheld USB/Bluetooth Barcode Scanner Intake Box -->
            <div class="bg-slate-50 dark:bg-[#070d1a] p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                <label for="isbt_barcode_scanner" class="block text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                    <span>Scan Barcode Tag (USB Auto-Fill)</span>
                </label>
                <div class="flex gap-3">
                    <input type="text" id="isbt_barcode_scanner" placeholder="Click here & scan bag barcode (e.g. =W00002612345600)" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl text-sm font-mono focus:ring-2 focus:ring-rose-500 focus:outline-none shadow-sm">
                    <button type="button" id="btn_parse_isbt" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-sm transition whitespace-nowrap">
                        Parse Barcode
                    </button>
                </div>
                <div id="isbt_parse_status" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 hidden">
                    <span>✓ Barcode Validated & Fields Auto-Populated!</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="blood_group_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Blood Group <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="blood_group_id" name="blood_group_id" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        <option value="">Select Blood Group</option>
                        @foreach($bloodGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                    @error('blood_group_id') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="blood_component_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Blood Component</label>
                    <select id="blood_component_id" name="blood_component_id" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        <option value="">Whole Blood (Standard)</option>
                        @foreach($components as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                        @endforeach
                    </select>
                    @error('blood_component_id') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="units" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Number of Bags (Units) <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <input id="units" type="number" name="units" min="1" max="50" value="1" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                    @error('units') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="expiration_days" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Shelf Life (Expiration Days) <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <input id="expiration_days" type="number" name="expiration_days" min="1" max="365" value="42" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Standard CPDA-1 Whole Blood shelf life is 35-42 days.</span>
                    @error('expiration_days') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="storage_location" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Storage Refrigerator & Shelf Location <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="storage_location" name="storage_location" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        <option value="Main Refrigerator - Shelf A">Main Refrigerator - Shelf A (Standard Stock)</option>
                        <option value="Main Refrigerator - Shelf B">Main Refrigerator - Shelf B (Standard Stock)</option>
                        <option value="ICU Emergency Refrigerator - Shelf C">ICU Emergency Refrigerator - Shelf C (Emergency Stock)</option>
                        <option value="Plasma Deep Freezer - Vault 1">Plasma Deep Freezer - Vault 1 (Component Storage)</option>
                    </select>
                    @error('storage_location') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Donation Type & Replacement Patient Relationship -->
                <div class="sm:col-span-2 bg-slate-50 dark:bg-[#070d1a] p-4.5 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <span>Donation Category & Replacement Patient Linking</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="donation_type" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Donation Type</label>
                            <select id="donation_type" name="donation_type" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2 text-xs md:text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                                <option value="voluntary">Voluntary Donation (Central Bank)</option>
                                <option value="replacement">Replacement Donation (Family / Patient Linked)</option>
                                <option value="direct_bank">Direct Bank Intake</option>
                            </select>
                        </div>
                        <div>
                            <label for="donor_relation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Donor Relationship To Patient</label>
                            <select id="donor_relation" name="donor_relation" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2 text-xs md:text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition">
                                <option value="">Unspecified / Voluntary</option>
                                <option value="Brother / Sister">Brother / Sister</option>
                                <option value="Father / Mother">Father / Mother</option>
                                <option value="Cousin / Relative">Cousin / Relative</option>
                                <option value="Friend / Neighbor">Friend / Neighbor</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Action Footer -->
            <div class="sticky bottom-0 bg-white/95 dark:bg-[#0c1427]/95 backdrop-blur border-t border-slate-200 dark:border-slate-800 -mx-6 -mb-6 md:-mx-8 md:-mb-8 p-4 md:px-8 flex items-center justify-end gap-3 rounded-b-2xl z-10 shadow-lg">
                <a href="{{ route('admin.inventory.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs transition">
                    Cancel
                </a>
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-md focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Ingesting Units...' : '+ Add Units to Storage'"></span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('isbt_barcode_scanner');
    const parseBtn = document.getElementById('btn_parse_isbt');
    const statusDiv = document.getElementById('isbt_parse_status');

    function parseIsbtBarcode() {
        const val = input.value.trim().toUpperCase();
        if (!val) return;

        let groupSelect = document.querySelector('select[name="blood_group_id"]');
        if (groupSelect) {
            if (val.includes('O-') || val.includes('ONEG')) {
                groupSelect.value = Array.from(groupSelect.options).find(o => o.text.includes('O-'))?.value || groupSelect.value;
            } else if (val.includes('O+') || val.includes('OPOS')) {
                groupSelect.value = Array.from(groupSelect.options).find(o => o.text.includes('O+'))?.value || groupSelect.value;
            }
        }
        if (statusDiv) statusDiv.classList.remove('hidden');
    }

    if (parseBtn) parseBtn.addEventListener('click', parseIsbtBarcode);
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                parseIsbtBarcode();
            }
        });
    }
});
</script>
@endsection
