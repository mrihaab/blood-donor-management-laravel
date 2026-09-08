@extends('layouts.admin')

@section('title', 'Send Notification Alert')
@section('page_title', 'Broadcast Notification Alert')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Broadcast Notification Alert</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Dispatch operational or emergency broadcast alerts to donor accounts.</p>
        </div>
        <a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 transition">
            &larr; Back to Alerts
        </a>
    </div>

    <!-- Form Container Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 shadow-sm transition-colors" 
         x-data="{ showConfirmModal: false, isSubmitting: false, titleVal: '{{ old('title') }}', targetVal: '{{ old('target', 'all') }}', messageVal: '{{ old('message') }}' }">
        
        <form id="notif-broadcast-form" method="POST" action="{{ route('admin.notifications.store') }}" class="space-y-6" @submit="isSubmitting = true">
            @csrf

            <!-- Notification Title -->
            <div>
                <label for="notif_title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Notification Title <span class="text-rose-600 font-bold">*</span>
                </label>
                <input id="notif_title" type="text" name="title" x-model="titleVal" required placeholder="e.g. Urgent O- Negative Blood Callout" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                @error('title') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Target Group -->
            <div>
                <label for="notif_target" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Target Donor Audience <span class="text-rose-600 font-bold">*</span>
                </label>
                <select id="notif_target" name="target" x-model="targetVal" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm">
                    <option value="all">All Registered Donors</option>
                    <option value="active">Active Donors Only</option>
                </select>
                @error('target') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Message Content -->
            <div>
                <label for="notif_message" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Message Body <span class="text-rose-600 font-bold">*</span>
                </label>
                <textarea id="notif_message" name="message" x-model="messageVal" rows="4" required placeholder="Enter clear, concise alert message for donors..." class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none placeholder-slate-400 dark:placeholder-slate-500 transition shadow-sm"></textarea>
                @error('message') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Form Actions with 2-Step Broadcast Confirmation -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.notifications.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs transition">
                    Cancel
                </a>
                <button type="button" 
                        @click="if (titleVal && messageVal) { showConfirmModal = true; } else { $el.closest('form').reportValidity(); }" 
                        class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-md focus:outline-none focus:ring-2 focus:ring-rose-500">
                    Send Broadcast Alert &rarr;
                </button>
            </div>

            <!-- Confirmation Modal for Mass Broadcast -->
            <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="w-full max-w-lg bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-left space-y-4" @click.away="showConfirmModal = false">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 58.625l-1.2 1.2M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Confirm Mass Notification Broadcast</span>
                        </h3>
                        <button type="button" @click="showConfirmModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-3 text-xs md:text-sm text-slate-700 dark:text-slate-300">
                        <div class="bg-slate-50 dark:bg-[#070d1a] p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                            <p><strong>Title:</strong> <span x-text="titleVal"></span></p>
                            <p><strong>Target Audience:</strong> <span class="font-bold text-rose-600 dark:text-rose-400" x-text="targetVal === 'all' ? 'All Registered Donors' : 'Active Donors Only'"></span></p>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed italic">
                            Executing this action will send a notification broadcast to the selected audience. Please confirm message accuracy before sending.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showConfirmModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting" 
                                @click="isSubmitting = true" 
                                class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition inline-flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50">
                            <template x-if="isSubmitting">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </template>
                            <span x-text="isSubmitting ? 'Dispatching Broadcast...' : 'Confirm & Dispatch Broadcast'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
