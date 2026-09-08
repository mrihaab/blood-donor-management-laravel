@extends('layouts.admin')

@section('title', 'Record Donation')
@section('page_title', 'Record New Blood Donation')

@section('content')
<div class="max-w-3xl space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Record Blood Donation</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Directly log a completed blood donation for a registered donor.</p>
        </div>
        <a href="{{ route('admin.donations.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
            Back to Directory
        </a>
    </div>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="p-4 text-xs md:text-sm text-rose-900 dark:text-rose-200 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 shadow-sm" role="alert">
            <div class="font-bold mb-1">Please correct the following input errors:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 md:p-8 space-y-6">
        <form method="POST" action="{{ route('admin.donations.store') }}" class="space-y-6" @submit="isSubmitting = true">
            @csrf

            <div class="space-y-4">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">Donation Entry Details</h3>
                
                <div>
                    <label for="donor_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Registered Donor <span class="text-rose-600">*</span></label>
                    <select id="donor_id" name="donor_id" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <option value="">-- Select Donor --</option>
                        @foreach($donors as $donor)
                            <option value="{{ $donor->id }}" {{ old('donor_id') == $donor->id ? 'selected' : '' }}>
                                {{ $donor->user->name ?? 'Donor #'.$donor->id }} ({{ $donor->bloodGroup->name ?? 'Group N/A' }}) - {{ $donor->city ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="quantity" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Collection Volume (mL) <span class="text-rose-600">*</span></label>
                        <input id="quantity" type="number" name="quantity" min="100" max="1000" value="{{ old('quantity') }}" placeholder="e.g. 450" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none font-mono">
                    </div>

                    <div>
                        <label for="donation_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Donation Date <span class="text-rose-600">*</span></label>
                        <input id="donation_date" type="date" name="donation_date" max="{{ date('Y-m-d') }}" value="{{ old('donation_date', date('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="collection_center" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Collection Center</label>
                    <input id="collection_center" type="text" name="collection_center" value="{{ old('collection_center', 'Central Blood Bank Lab') }}" placeholder="Location or facility name" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>
            </div>

            <!-- Form Action Footer -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.donations.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Recording Donation...' : 'Record & Add to Inventory'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
