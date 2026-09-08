@extends('layouts.admin')

@section('title', 'Manage Service Cities')

@section('content')
<div class="space-y-6 max-w-4xl" x-data="{ addModalOpen: false }">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Settings', 'url' => route('admin.settings.index')],
        ['label' => 'Operational Cities']
    ]" />

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-6 border-b border-slate-200 dark:border-slate-800 text-sm font-semibold">
        <a href="{{ route('admin.settings.index') }}" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 pb-3 transition">General Settings</a>
        <a href="{{ route('admin.settings.blood_groups') }}" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 pb-3 transition">Blood Groups</a>
        <a href="{{ route('admin.settings.cities') }}" class="text-rose-600 dark:text-rose-400 border-b-2 border-rose-600 dark:border-rose-400 pb-3 font-bold">Operational Cities</a>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Active Operational Cities</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Configure service regions where blood bank operations, hospital requisitions, and donor matching are active.</p>
        </div>
        <button type="button" 
                @click="addModalOpen = true" 
                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-sm transition inline-flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-rose-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add New City</span>
        </button>
    </div>

    @if (session('success'))
        <div class="p-4 text-xs md:text-sm text-emerald-800 dark:text-emerald-300 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Main List Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-4">
        <ul class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs md:text-sm text-slate-700 dark:text-slate-300 font-medium">
            @forelse($cities as $index => $city)
                <li class="py-3.5 flex items-center justify-between hover:bg-slate-50/70 dark:hover:bg-slate-800/40 px-3 rounded-xl transition">
                    <div class="flex items-center space-x-3">
                        <span class="text-base shrink-0">📍</span>
                        <span class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $city }}</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-xs px-2.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 rounded-full font-bold border border-emerald-200 dark:border-emerald-800/60">Active</span>
                        
                        <!-- Delete City Form & Trigger -->
                        <form id="delete-city-form-{{ $index }}" method="POST" action="{{ route('admin.settings.cities.destroy', $index) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                        <button type="button" 
                                onclick="window.dispatchEvent(new CustomEvent('open-confirm', { detail: { modalId: 'confirm-delete-city-{{ $index }}', formId: 'delete-city-form-{{ $index }}' } }))"
                                class="text-xs text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 font-bold transition">
                            Delete
                        </button>

                        <x-confirm-dialog 
                            id="confirm-delete-city-{{ $index }}"
                            title="Remove Operational City?"
                            message="Are you sure you want to remove '{{ $city }}' from the active operational regions? Existing donor records in this city will remain unchanged, but new registrations may be affected."
                            confirmText="Yes, Remove City"
                            cancelText="Cancel"
                            variant="danger"
                        />
                    </div>
                </li>
            @empty
                <li class="py-8 text-center">
                    <x-empty-state 
                        title="No Operational Cities Configured" 
                        description="Add an operational city to enable region-based donor matching and hospital fulfillment."
                    />
                </li>
            @endforelse
        </ul>
    </div>

    <!-- Add City Modal -->
    <div x-show="addModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4"
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="add-city-modal-title"
         @keydown.escape.window="addModalOpen = false">
        
        <div class="bg-white dark:bg-[#0c1427] rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200 dark:border-slate-800 transform transition-all"
             @click.outside="addModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 id="add-city-modal-title" class="text-base font-extrabold text-slate-900 dark:text-white">Add Operational City</h3>
                <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold transition text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.settings.cities.store') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="space-y-4">
                @csrf
                <div>
                    <label for="city_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">City Name *</label>
                    <input type="text" 
                           id="city_name"
                           name="city_name" 
                           required 
                           placeholder="e.g. Sialkot" 
                           class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-xs md:text-sm font-medium focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                    <button type="submit" 
                            :disabled="isSubmitting" 
                            class="px-4 py-2 text-xs font-extrabold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition disabled:opacity-50 inline-flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <template x-if="isSubmitting">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                        <span x-text="isSubmitting ? 'Adding...' : 'Add City'">Add City</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
