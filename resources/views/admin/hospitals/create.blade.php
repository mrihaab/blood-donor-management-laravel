@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Register New Hospital & Staff Account</h1>
            <p class="text-sm text-slate-500">Add an accredited hospital institution and provision primary clinician login credentials.</p>
        </div>
        <a href="{{ route('admin.hospitals.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Back to Directory</a>
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

    <form method="POST" action="{{ route('admin.hospitals.store') }}" class="space-y-6">
        @csrf

        <!-- Hospital Entity Information -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span>🏥</span> Hospital Organization Information
            </h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Hospital Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. City General Hospital" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">License Number *</label>
                    <input type="text" name="license_number" value="{{ old('license_number') }}" required placeholder="e.g. LIC-HOSP-2026-99" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Official Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="info@cityhospital.org" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Phone *</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" required placeholder="+1 (555) 019-2834" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">City *</label>
                    <input type="text" name="city" value="{{ old('city') }}" required placeholder="e.g. New York" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Status *</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Full Physical Address *</label>
                <textarea name="address" rows="2" required placeholder="123 Healthcare Ave, Medical District, NY 10001" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">{{ old('address') }}</textarea>
            </div>
        </div>

        <!-- Primary Staff User Credentials -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span>👤</span> Primary Hospital Staff Login Credentials
            </h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Staff Member Name *</label>
                    <input type="text" name="staff_name" value="{{ old('staff_name') }}" required placeholder="Dr. Sarah Connor" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Staff Login Email *</label>
                    <input type="email" name="staff_email" value="{{ old('staff_email') }}" required placeholder="sarah.connor@cityhospital.org" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="Minimum 8 characters" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.hospitals.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
            <button type="submit" class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-red-700 shadow-md transition">
                Register Hospital & Create Staff User
            </button>
        </div>
    </form>
</div>
@endsection
