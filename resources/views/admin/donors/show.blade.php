@extends('layouts.admin')

@section('title', 'Donor Details')
@section('page_title', 'Donor Profile Record')

@section('content')
<div class="max-w-4xl space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Header & Action Controls -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $donor->user->name ?? 'Donor Account' }}</h1>
                <x-status-badge :status="$donor->status === 'active' ? 'active' : 'expired'" :label="ucfirst($donor->status ?? 'active')" />
            </div>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1 font-mono">{{ $donor->user->email ?? 'No email assigned' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.donors.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
                Back to Directory
            </a>
            <a href="{{ route('admin.donors.edit', $donor->id) }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-sm transition">
                Edit Profile
            </a>
            <form id="form-delete-donor-show" method="POST" action="{{ route('admin.donors.destroy', $donor->id) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-delete-donor-show', formId: 'form-delete-donor-show' })" class="px-3 py-2.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 hover:bg-rose-100 text-xs font-bold rounded-xl transition">
                    Delete
                </button>
            </form>
            <x-confirm-dialog id="confirm-delete-donor-show" title="Delete Donor Record" message="Are you sure you want to delete donor '{{ $donor->user->name ?? 'Donor' }}'? This action cannot be undone." confirmText="Delete Account" variant="danger" />
        </div>
    </div>

    <!-- Overview & ABO/Rh Summary Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 md:p-8 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
            <div class="space-y-1">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">ABO/Rh Blood Type Profile</span>
                <div class="text-3xl font-mono font-black text-rose-600 dark:text-rose-400">
                    {{ $donor->bloodGroup->name ?? 'Unclassified' }}
                </div>
            </div>
            <div class="text-right space-y-1">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">System Registration Date</span>
                <p class="text-xs font-mono font-semibold text-slate-700 dark:text-slate-300">
                    {{ $donor->created_at ? $donor->created_at->format('M d, Y') : 'N/A' }}
                </p>
            </div>
        </div>

        <!-- Section 1: Demographics & Medical Attributes -->
        <div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-3">Medical & Demographic Profile</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs bg-slate-50 dark:bg-[#111c38] p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Gender</span>
                    <span class="font-bold text-slate-900 dark:text-white capitalize">{{ $donor->gender ?? 'Not Specified' }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Date of Birth</span>
                    <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $donor->date_of_birth ?? 'Not Specified' }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Assigned System ID</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">DNR-{{ str_pad($donor->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Contact & Geographical Location -->
        <div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-3">Contact Details & Location</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-slate-50 dark:bg-[#111c38] p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Contact Phone Number</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $donor->contact_number ?? 'Not Provided' }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">City & Province</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $donor->city ?? 'N/A' }}, {{ $donor->state ?? 'N/A' }} ({{ $donor->zip_code ?? '54000' }})</span>
                </div>
                <div class="sm:col-span-2 border-t border-slate-200 dark:border-slate-800/80 pt-2 mt-1">
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Full Street Address</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $donor->address ?? 'Not Provided' }}</span>
                </div>
            </div>
        </div>

        <!-- Section 3: Recorded Donation History -->
        <div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-3">Recorded Donation History</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Unit Barcode</th>
                            <th class="px-4 py-3">Donation Date</th>
                            <th class="px-4 py-3">Volume Collected</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                        @forelse($donor->donations ?? [] as $donation)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <td class="px-4 py-3 font-mono font-bold text-rose-600 dark:text-rose-400">
                                    {{ $donation->unit_number ?? 'UN-'.$donation->id }}
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-600 dark:text-slate-400">
                                    {{ $donation->donation_date ? \Carbon\Carbon::parse($donation->donation_date)->format('M d, Y') : $donation->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 font-mono">
                                    {{ $donation->quantity ?? $donation->volume_ml ?? 450 }} mL
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="($donation->status ?? 'completed') === 'completed' ? 'approved' : 'pending'" :label="ucfirst($donation->status ?? 'completed')" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500 italic">No prior donation records found for this donor profile.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
