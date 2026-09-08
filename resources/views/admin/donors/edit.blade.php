@extends('layouts.admin')

@section('title', 'Edit Donor Profile')
@section('page_title', 'Edit Donor Profile')

@section('content')
<div class="max-w-4xl space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Page Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Edit Donor Profile ({{ $donor->user->name ?? 'Donor' }})</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Update demographic classification, ABO/Rh type, and contact details.</p>
        </div>
        <a href="{{ route('admin.donors.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
            Back to Directory
        </a>
    </div>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="p-4 text-xs md:text-sm text-rose-900 dark:text-rose-200 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 shadow-sm" role="alert">
            <div class="font-bold mb-1">Please correct the following errors:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 md:p-8 space-y-6">
        <form method="POST" action="{{ route('admin.donors.update', $donor->id) }}" class="space-y-6" @submit="isSubmitting = true">
            @csrf
            @method('PUT')

            <!-- Section 1: Account Credentials -->
            <div class="space-y-4">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">1. Account Credentials</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Full Name <span class="text-rose-600">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name', $donor->user->name ?? '') }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Email Address <span class="text-rose-600">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email', $donor->user->email ?? '') }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Section 2: Medical & ABO/Rh Profile -->
            <div class="space-y-4 pt-2">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">2. ABO/Rh Classification & Demographic Profile</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="blood_group_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Blood Group <span class="text-rose-600">*</span></label>
                        <select id="blood_group_id" name="blood_group_id" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            @foreach($bloodGroups as $group)
                                <option value="{{ $group->id }}" {{ old('blood_group_id', $donor->blood_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="gender" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Gender <span class="text-rose-600">*</span></label>
                        <select id="gender" name="gender" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            <option value="male" {{ old('gender', $donor->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $donor->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $donor->gender) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Date of Birth <span class="text-rose-600">*</span></label>
                        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $donor->date_of_birth) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Section 3: Contact & Location Profile -->
            <div class="space-y-4 pt-2">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">3. Contact & Address Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="contact_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Contact Phone Number <span class="text-rose-600">*</span></label>
                        <input id="contact_number" type="text" name="contact_number" value="{{ old('contact_number', $donor->contact_number) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="city" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">City <span class="text-rose-600">*</span></label>
                        <input id="city" type="text" name="city" value="{{ old('city', $donor->city) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="state" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">State / Province <span class="text-rose-600">*</span></label>
                        <input id="state" type="text" name="state" value="{{ old('state', $donor->state) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-3">
                        <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Street Address <span class="text-rose-600">*</span></label>
                        <input id="address" type="text" name="address" value="{{ old('address', $donor->address) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="zip_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Postal / Zip Code <span class="text-rose-600">*</span></label>
                        <input id="zip_code" type="text" name="zip_code" value="{{ old('zip_code', $donor->zip_code ?? '54000') }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Form Action Footer -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.donors.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Updating Profile...' : 'Save Profile Changes'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
