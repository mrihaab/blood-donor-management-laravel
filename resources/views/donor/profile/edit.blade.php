@extends('layouts.donor')

@section('title', 'My Profile')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex items-center justify-between transition-colors">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Donor Profile Settings</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Manage your contact information, blood profile, and geographic location details.</p>
        </div>
        <x-status-badge status="active" label="Verified Donor" />
    </div>

    <!-- Error Summary Block -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/90 border-l-4 border-rose-500 text-rose-800 dark:text-rose-200 shadow-sm space-y-1.5" role="alert">
            <div class="flex items-center gap-2 font-bold text-xs md:text-sm">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Please correct the highlighted errors:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Profile Edit Form Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 shadow-sm transition-colors">
        <form method="POST" action="{{ route('donor.profile.update') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Section 1: Account Credentials & Identity -->
            <div class="space-y-4">
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">
                    1. Account Credentials & Identity
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Name (Readonly system account) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Full Account Name
                        </label>
                        <input type="text" value="{{ Auth::user()->name }}" disabled class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-slate-100 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-800 shadow-sm font-medium cursor-not-allowed">
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 block">Account identity managed by central system user profile.</span>
                    </div>

                    <!-- Email Address (Readonly system account) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Email Address
                        </label>
                        <input type="email" value="{{ Auth::user()->email }}" disabled class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-slate-100 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-800 shadow-sm font-medium cursor-not-allowed font-mono">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Gender <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <select id="gender" name="gender" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                            <option value="male" {{ old('gender', $donor->gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $donor->gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $donor->gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Date of Birth <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', is_object($donor->date_of_birth ?? null) ? $donor->date_of_birth->format('Y-m-d') : ($donor->date_of_birth ?? '')) }}" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm font-mono">
                        @error('date_of_birth') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Donor Blood Profile & Contact Details -->
            <div class="space-y-4">
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">
                    2. Donor Blood Profile & Contact
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Blood Group Select -->
                    <div>
                        <label for="blood_group_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Blood Group <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <select id="blood_group_id" name="blood_group_id" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm font-bold">
                            @foreach($bloodGroups as $group)
                                <option value="{{ $group->id }}" {{ old('blood_group_id', $donor->blood_group_id ?? null) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-amber-600 dark:text-amber-400 mt-1 block">Updating blood group modifies emergency broadcast targeting.</span>
                        @error('blood_group_id') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Contact Number -->
                    <div>
                        <label for="contact_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Contact Phone Number <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input id="contact_number" type="text" name="contact_number" value="{{ old('contact_number', $donor->contact_number ?? '') }}" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm font-mono">
                        @error('contact_number') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Geographic & Location Profile -->
            <div class="space-y-4">
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">
                    3. Address & Regional Location
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- City -->
                    <div>
                        <label for="city" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            City <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input id="city" type="text" name="city" value="{{ old('city', $donor->city ?? '') }}" placeholder="e.g. Lahore" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        @error('city') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- State / Province -->
                    <div>
                        <label for="state" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            State / Province <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input id="state" type="text" name="state" value="{{ old('state', $donor->state ?? '') }}" placeholder="e.g. Punjab" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        @error('state') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Full Street Address -->
                <div>
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Street Address <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <textarea id="address" name="address" rows="2" placeholder="House/Apartment #, Street, Sector..." required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">{{ old('address', $donor->address ?? '') }}</textarea>
                    @error('address') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs md:text-sm font-extrabold rounded-xl shadow-md transition flex items-center gap-2">
                    <span x-text="isSubmitting ? 'Saving Profile...' : 'Save Profile Changes'">Save Profile Changes</span>
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
