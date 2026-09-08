@extends('layouts.admin')

@section('title', 'Edit Inventory Stock')
@section('page_title', 'Edit Inventory Batch')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Edit Inventory Batch #{{ $inventory->id }}</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Update central vault stock quantities and batch statuses.</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 transition">
            &larr; Back to Inventory
        </a>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 shadow-sm transition-colors">
        <form method="POST" action="{{ route('admin.inventory.update', $inventory->id) }}" class="space-y-6" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Blood Group Selection -->
                <div>
                    <label for="blood_group_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Blood Group <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="blood_group_id" name="blood_group_id" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        @foreach($bloodGroups as $group)
                            <option value="{{ $group->id }}" {{ $inventory->blood_group_id == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                    @error('blood_group_id') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Quantity Units -->
                <div>
                    <label for="quantity" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Quantity (Units) <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <input id="quantity" type="number" name="quantity" min="0" value="{{ old('quantity', $inventory->quantity) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                    @error('quantity') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Batch Status -->
                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Batch Availability Status <span class="text-rose-600 font-bold">*</span>
                    </label>
                    <select id="status" name="status" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                        <option value="available" {{ $inventory->status == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="reserved" {{ $inventory->status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="used" {{ $inventory->status == 'used' ? 'selected' : '' }}>Used</option>
                        <option value="expired" {{ $inventory->status == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                    @error('status') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.inventory.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs transition">
                    Cancel
                </a>
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-md focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Saving Changes...' : 'Update Stock Batch'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
