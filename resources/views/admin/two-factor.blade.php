@extends('layouts.admin')

@section('title', 'Two-Factor Authentication')
@section('page_title', 'Admin Two-Factor Security (2FA)')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
    <div>
        <h3 class="text-lg font-bold text-gray-900">TOTP Two-Factor Authentication</h3>
        <p class="text-sm text-gray-500 mt-1">Enhance account security by requiring a 6-digit TOTP verification code from an authenticator app (Google Authenticator, Authy, 1Password) upon login.</p>
    </div>

    @if($user->google2fa_enabled)
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
            <strong>2FA Status: ENABLED</strong> — Your admin account is protected with Two-Factor Authentication.
        </div>

        @if(!empty($recoveryCodes))
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 space-y-2">
                <h4 class="font-bold text-gray-800 text-sm">Emergency Recovery Codes</h4>
                <p class="text-xs text-gray-500">Store these recovery codes securely. If you lose access to your authenticator app, each code can be used once to gain emergency access.</p>
                <div class="grid grid-cols-2 gap-2 font-mono text-xs text-gray-700 pt-2">
                    @foreach($recoveryCodes as $code)
                        <div class="bg-white p-2 rounded border border-gray-200 select-all">{{ $code }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.2fa.disable') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Disable 2FA</button>
        </form>
    @else
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-sm">
            <strong>2FA Status: DISABLED</strong> — Scan the QR code below with your authenticator app, then enter the generated 6-digit code to enable 2FA.
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Secret Key (Manual Entry)</label>
                <code class="block p-2 bg-gray-100 rounded text-sm font-mono text-gray-800 mt-1">{{ $secret }}</code>
            </div>

            <form method="POST" action="{{ route('admin.2fa.enable') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Enter 6-Digit Code from Authenticator App</label>
                    <input type="text" name="code" maxlength="6" required placeholder="123456" class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 font-mono text-center text-lg">
                    @error('code')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Verify & Enable 2FA</button>
            </form>
        </div>
    @endif
</div>
@endsection
