<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quick Role Login - LifeBlood Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="inline-flex p-3 bg-red-100 text-red-600 rounded-full">
                <svg class="w-10 h-10 fill-current" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">LifeBlood Portal Switch</h2>
            <p class="text-sm text-gray-500">Instant role login for testing & demonstration</p>
        </div>

        @if ($errors->any())
            <div class="p-4 bg-red-50 text-red-700 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="space-y-4">
            <!-- Quick Admin Login Card -->
            <form method="POST" action="{{ url('/login-as') }}" class="p-4 rounded-xl border border-purple-100 bg-purple-50/50 flex items-center justify-between">
                @csrf
                <input type="hidden" name="role" value="admin">
                <input type="hidden" name="email" value="admin@example.com">
                <div>
                    <span class="px-2.5 py-0.5 bg-purple-200 text-purple-800 text-xs font-bold rounded-full">ADMIN PORTAL</span>
                    <p class="font-semibold text-gray-900 text-sm mt-1">Administrator Account</p>
                    <p class="text-xs text-gray-500">admin@example.com</p>
                </div>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-lg transition shadow-sm">
                    Login &rarr;
                </button>
            </form>

            <!-- Quick Donor Login Card -->
            <form method="POST" action="{{ url('/login-as') }}" class="p-4 rounded-xl border border-red-100 bg-red-50/50 flex items-center justify-between">
                @csrf
                <input type="hidden" name="role" value="donor">
                <input type="hidden" name="email" value="donor@example.com">
                <div>
                    <span class="px-2.5 py-0.5 bg-red-200 text-red-800 text-xs font-bold rounded-full">DONOR PORTAL</span>
                    <p class="font-semibold text-gray-900 text-sm mt-1">Donor Account</p>
                    <p class="text-xs text-gray-500">donor@example.com</p>
                </div>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-sm">
                    Login &rarr;
                </button>
            </form>
        </div>

        <div class="text-center pt-2">
            <a href="/" class="text-xs text-gray-500 hover:underline">&larr; Back to Welcome Page</a>
        </div>
    </div>
</body>
</html>
