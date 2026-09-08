@extends('layouts.admin')

@section('title', 'Register Hospital')
@section('page_title', 'Register New Hospital Institution')

@section('content')
<div class="max-w-4xl space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Register Hospital Institution & Provision Primary Staff</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Add an accredited hospital entity and provision primary clinician login credentials.</p>
        </div>
        <a href="{{ route('admin.hospitals.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
            Back to Directory
        </a>
    </div>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="p-4 text-xs md:text-sm text-rose-900 dark:text-rose-200 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 shadow-sm" role="alert">
            <div class="font-bold mb-1">Please correct the following registration errors:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Registration Form Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 md:p-8 space-y-6">
        <form method="POST" action="{{ route('admin.hospitals.store') }}" class="space-y-6" @submit="isSubmitting = true">
            @csrf

            <!-- Section 1: Hospital Institution Profile -->
            <div class="space-y-4">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">1. Hospital Organization Profile</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Hospital Name <span class="text-rose-600">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Mayo Hospital Lahore" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="license_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Official License Number <span class="text-rose-600">*</span></label>
                        <input id="license_number" type="text" name="license_number" value="{{ old('license_number') }}" required placeholder="e.g. LIC-HOSP-2026-99" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Official Institution Email <span class="text-rose-600">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="info@mayohospital.org" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="contact_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Emergency Contact Phone <span class="text-rose-600">*</span></label>
                        <input id="contact_phone" type="text" name="contact_phone" value="{{ old('contact_phone') }}" required placeholder="+92 42 99211101" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="city" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">City <span class="text-rose-600">*</span></label>
                        <input id="city" type="text" name="city" value="{{ old('city') }}" required placeholder="e.g. Lahore" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Account Status <span class="text-rose-600">*</span></label>
                        <select id="status" name="status" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Full Physical Address <span class="text-rose-600">*</span></label>
                    <textarea id="address" name="address" rows="2" required placeholder="Hospital street address and medical district" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">{{ old('address') }}</textarea>
                </div>
            </div>

            <!-- Section 2: Primary Clinician / Staff Credentials -->
            <div class="space-y-4 pt-2">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">2. Primary Hospital Staff User Account</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="staff_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Staff Clinician Name <span class="text-rose-600">*</span></label>
                        <input id="staff_name" type="text" name="staff_name" value="{{ old('staff_name') }}" required placeholder="Dr. Sarah Connor" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="staff_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Staff Login Email <span class="text-rose-600">*</span></label>
                        <input id="staff_email" type="email" name="staff_email" value="{{ old('staff_email') }}" required placeholder="sarah.connor@mayohospital.org" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Password <span class="text-rose-600">*</span></label>
                        <input id="password" type="password" name="password" required placeholder="Minimum 8 characters" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Confirm Password <span class="text-rose-600">*</span></label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Repeat password" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Form Action Footer -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.hospitals.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Registering Hospital...' : 'Register Hospital & Staff User'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
