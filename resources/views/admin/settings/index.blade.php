@extends('layouts.admin')

@section('title', 'System Settings')
@section('page_title', 'System Settings & Operational Controls')

@section('content')
<div class="space-y-6 max-w-4xl pb-12" x-data="{ isSubmitting: false }">
    <!-- Sub-Navigation Header Tabs -->
    <div class="flex space-x-4 border-b border-slate-200 dark:border-slate-800 pb-3">
        <a href="{{ route('admin.settings.index') }}" class="text-xs md:text-sm font-extrabold text-rose-600 dark:text-rose-400 border-b-2 border-rose-600 dark:border-rose-400 pb-2">General Settings</a>
        <a href="{{ route('admin.settings.blood_groups') }}" class="text-xs md:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition pb-2">Blood Group Thresholds</a>
        <a href="{{ route('admin.settings.cities') }}" class="text-xs md:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition pb-2">City Coverage</a>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="p-4 text-xs md:text-sm text-emerald-900 dark:text-emerald-200 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between shadow-sm" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span><strong class="font-bold">Success!</strong> {{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 text-xs md:text-sm text-rose-900 dark:text-rose-200 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 flex items-center justify-between shadow-sm" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><strong class="font-bold">Error:</strong> {{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- System Configuration Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 md:p-8 space-y-6">
        <div>
            <h3 class="font-extrabold text-slate-900 dark:text-white text-lg tracking-tight">Organization Profile & Operational Parameters</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Configure central system details, inventory alert thresholds, and donor eligibility intervals.</p>
        </div>

        <form id="form-update-settings" method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6" @submit="isSubmitting = true">
            @csrf
            @method('PUT')

            <!-- Section: Organization Profile -->
            <div class="space-y-4">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">Organization Details</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="organization_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Organization Name <span class="text-rose-600">*</span></label>
                        <input id="organization_name" type="text" name="organization_name" value="{{ old('organization_name', \App\Models\SystemSetting::get('organization_name', 'LifeBlood Management System')) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="organization_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Organization Email <span class="text-rose-600">*</span></label>
                        <input id="organization_email" type="email" name="organization_email" value="{{ old('organization_email', \App\Models\SystemSetting::get('organization_email', 'admin@blood.com')) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="organization_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Emergency Helpline Phone <span class="text-rose-600">*</span></label>
                        <input id="organization_phone" type="text" name="organization_phone" value="{{ old('organization_phone', \App\Models\SystemSetting::get('organization_phone', '+92 42 111 222 333')) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="organization_address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Headquarters Address <span class="text-rose-600">*</span></label>
                        <input id="organization_address" type="text" name="organization_address" value="{{ old('organization_address', \App\Models\SystemSetting::get('organization_address', 'Central Blood Bank Complex, Lahore')) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Section: Inventory & Clinical Rules -->
            <div class="space-y-4 pt-2">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">Inventory Threshold & Clinical Rules</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="low_stock_threshold" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Low Stock Alert Threshold (Units) <span class="text-rose-600">*</span></label>
                        <input id="low_stock_threshold" type="number" name="low_stock_threshold" min="1" value="{{ old('low_stock_threshold', \App\Models\SystemSetting::get('low_stock_threshold', 10)) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Triggers low inventory status badge when unit count drops below this value.</p>
                    </div>

                    <div>
                        <label for="donation_interval_days" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Donation Interval Requirement (Days) <span class="text-rose-600">*</span></label>
                        <input id="donation_interval_days" type="number" name="donation_interval_days" min="1" value="{{ old('donation_interval_days', \App\Models\SystemSetting::get('donation_interval_days', 90)) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Minimum mandatory cooldown period between donor blood donations.</p>
                    </div>
                </div>
            </div>

            <!-- Section: System Notifications -->
            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">Automated Notifications</h4>
                
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="email_notifications" value="1" {{ \App\Models\SystemSetting::get('email_notifications', true) ? 'checked' : '' }} class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 dark:bg-slate-900 dark:border-slate-700">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Enable Automated Email Broadcasts for Emergency Requisitions</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="sms_notifications" value="1" {{ \App\Models\SystemSetting::get('sms_notifications', true) ? 'checked' : '' }} class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 dark:bg-slate-900 dark:border-slate-700">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Enable SMS Dispatch Confirmations & Urgent Donor Alerts</span>
                    </label>
                </div>
            </div>

            <!-- Form Action Footer -->
            <div class="flex items-center justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" 
                        @click="$dispatch('open-confirm', { modalId: 'confirm-update-settings', formId: 'form-update-settings' })"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-rose-500">
                    Save System Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Safety Confirmation Modal -->
    <x-confirm-dialog id="confirm-update-settings" 
                      title="Update System Settings & Thresholds" 
                      message="Are you sure you want to save these system configuration changes? Stock alert thresholds and donor eligibility intervals will apply system-wide immediately." 
                      confirmText="Apply Settings Changes" 
                      variant="primary" />
</div>
@endsection
