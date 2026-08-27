@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Hospital Organization</h1>
            <p class="text-sm text-slate-500">Update institutional details, contact phone, and status for {{ $hospital->name }}.</p>
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

    <form method="POST" action="{{ route('admin.hospitals.update', $hospital) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span>🏥</span> Hospital Details
            </h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Hospital Name *</label>
                    <input type="text" name="name" value="{{ old('name', $hospital->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">License Number *</label>
                    <input type="text" name="license_number" value="{{ old('license_number', $hospital->license_number) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Official Email *</label>
                    <input type="email" name="email" value="{{ old('email', $hospital->email) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Phone *</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $hospital->contact_phone) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">City *</label>
                    <input type="text" name="city" value="{{ old('city', $hospital->city) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Status *</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                        <option value="active" {{ old('status', $hospital->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $hospital->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Full Physical Address *</label>
                <textarea name="address" rows="2" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">{{ old('address', $hospital->address) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.hospitals.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
            <button type="submit" class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-red-700 shadow-md transition">
                Update Hospital Details
            </button>
        </div>
    </form>
</div>
@endsection
