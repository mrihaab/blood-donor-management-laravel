@extends('layouts.admin')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="space-y-6 max-w-4xl">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Two-Factor Security (2FA)']
    ]" />

    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Admin Two-Factor Security (2FA)</h1>
        <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Enhance account access controls by enforcing a 6-digit TOTP verification code from your mobile authenticator app upon login.</p>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6">
        @if(session('success'))
            <div class="p-4 text-xs md:text-sm text-emerald-800 dark:text-emerald-300 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 text-xs md:text-sm text-rose-800 dark:text-rose-300 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800/60 font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($user->google2fa_enabled)
            <!-- 2FA Enabled Banner -->
            <div class="p-5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 rounded-2xl text-emerald-900 dark:text-emerald-200 flex items-start gap-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 rounded-xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold">2FA Status: ENABLED</h2>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5 leading-relaxed">Your administrator account is protected with Two-Factor Authentication. A TOTP code will be required during each session sign-in.</p>
                </div>
            </div>

            @if(!empty($recoveryCodes))
                <div class="p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700/60 space-y-3">
                    <div>
                        <h2 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                            <span>🔑 Emergency Recovery Codes</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Store these emergency recovery codes in a secure secret manager. If you lose access to your mobile authenticator device, each code can be used once for emergency sign-in.</p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono text-xs text-slate-800 dark:text-slate-200 pt-2">
                        @foreach($recoveryCodes as $code)
                            <div class="bg-white dark:bg-slate-900 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 select-all font-bold text-center tracking-wider shadow-2xs">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Disable 2FA Form -->
            <form id="disable-2fa-form" method="POST" action="{{ route('admin.2fa.disable') }}" class="hidden">
                @csrf
            </form>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="button" 
                        onclick="window.dispatchEvent(new CustomEvent('open-confirm', { detail: { modalId: 'confirm-disable-2fa', formId: 'disable-2fa-form' } }))"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition focus:outline-none focus:ring-2 focus:ring-rose-500">
                    Disable Two-Factor Security
                </button>
            </div>

            <!-- Confirm Dialog for Disable 2FA -->
            <x-confirm-dialog 
                id="confirm-disable-2fa"
                title="Disable Two-Factor Authentication?"
                message="Are you sure you want to disable 2FA for your account? Your administrative account will no longer require a secondary verification code during sign-in."
                confirmText="Yes, Disable 2FA"
                cancelText="Keep 2FA Active"
                variant="danger"
            />
        @else
            <!-- 2FA Disabled Banner -->
            <div class="p-5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 rounded-2xl text-amber-900 dark:text-amber-200 flex items-start gap-4">
                <div class="p-3 bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 rounded-xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold">2FA Status: DISABLED</h2>
                    <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5 leading-relaxed">Scan the secret key with your authenticator app (e.g. Google Authenticator, Authy, 1Password), then enter the generated 6-digit TOTP code below to activate security enforcement.</p>
                </div>
            </div>

            <!-- Setup Instructions & Verification Form -->
            <div class="space-y-5 pt-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Secret Key (Manual Input)</label>
                    <div class="p-3 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 font-mono text-sm text-slate-800 dark:text-slate-200 select-all tracking-wider font-bold">
                        {{ $secret }}
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.2fa.enable') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="space-y-4 max-w-md">
                    @csrf
                    <div>
                        <label for="totp_code" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Enter 6-Digit Authenticator Code *</label>
                        <input type="text" 
                               id="totp_code"
                               name="code" 
                               maxlength="6" 
                               required 
                               placeholder="123456" 
                               autocomplete="off"
                               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-4 py-2.5 text-center font-mono text-xl font-bold tracking-widest focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                        @error('code')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" 
                            :disabled="isSubmitting" 
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold rounded-xl shadow-sm transition disabled:opacity-50 inline-flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <template x-if="isSubmitting">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                        <span x-text="isSubmitting ? 'Verifying Code...' : 'Verify & Enable 2FA'">Verify & Enable 2FA</span>
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
