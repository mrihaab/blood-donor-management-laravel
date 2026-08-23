<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LifeBlood — Professional Blood Bank & Donor Operations Platform</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="h-full text-slate-900 antialiased bg-slate-50">
    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-red-700 to-red-500 text-white font-black shadow-md">LB</div>
                <div>
                    <span class="text-base font-extrabold text-slate-900 tracking-tight block">LifeBlood</span>
                    <span class="text-[10px] font-bold text-red-600 block uppercase tracking-wider">Blood Bank & Donor Platform</span>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('donor.dashboard') }}" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition">
                            Open Console &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900">Sign In</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition shadow-sm">
                                Register as Donor
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20 bg-gradient-to-b from-white to-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-block rounded-full bg-red-100 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-red-700">Clinical Operations System</span>
            <h1 class="mt-4 text-4xl font-black tracking-tight text-slate-900 sm:text-6xl">
                Lifesaving Blood Bank Operations <br class="hidden sm:inline" /> & Inventory Management
            </h1>
            <p class="mx-auto mt-4 max-w-2xl text-base text-slate-600">
                Unit-level barcode bag tracking, component preservation, 56-day donor eligibility enforcement, and real-time hospital requisitions.
            </p>
            <div class="mt-8 flex justify-center gap-4">
                <a href="{{ route('register') }}" class="rounded-xl bg-red-600 px-6 py-3.5 text-base font-bold text-white shadow-md hover:bg-red-700 transition">
                    Become a Donor Today
                </a>
                <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-base font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                    Hospital Portal Access
                </a>
            </div>
        </div>
    </section>

    <!-- Medical Components Grid -->
    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl font-extrabold text-slate-900">Supported Medical Blood Components</h2>
            <p class="text-sm text-slate-500 mt-1">Preserved according to clinical storage specifications and dynamic shelf-life rules.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm text-center">
                <span class="inline-block rounded-md bg-red-700 px-2.5 py-1 text-xs font-bold text-white">WB</span>
                <h3 class="mt-3 text-sm font-bold text-slate-900">Whole Blood</h3>
                <p class="mt-1 text-xs text-slate-500">2°C - 6°C | 35 Days</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm text-center">
                <span class="inline-block rounded-md bg-red-700 px-2.5 py-1 text-xs font-bold text-white">PRBC</span>
                <h3 class="mt-3 text-sm font-bold text-slate-900">Packed Red Cells</h3>
                <p class="mt-1 text-xs text-slate-500">2°C - 6°C | 42 Days</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm text-center">
                <span class="inline-block rounded-md bg-amber-600 px-2.5 py-1 text-xs font-bold text-white">PLT</span>
                <h3 class="mt-3 text-sm font-bold text-slate-900">Platelets</h3>
                <p class="mt-1 text-xs text-slate-500">20°C - 24°C | 5 Days</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm text-center">
                <span class="inline-block rounded-md bg-blue-600 px-2.5 py-1 text-xs font-bold text-white">FFP</span>
                <h3 class="mt-3 text-sm font-bold text-slate-900">Fresh Frozen Plasma</h3>
                <p class="mt-1 text-xs text-slate-500">-18°C | 365 Days</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm text-center">
                <span class="inline-block rounded-md bg-purple-600 px-2.5 py-1 text-xs font-bold text-white">CRYO</span>
                <h3 class="mt-3 text-sm font-bold text-slate-900">Cryoprecipitate</h3>
                <p class="mt-1 text-xs text-slate-500">-18°C | 365 Days</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 text-sm">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="font-bold text-white">LifeBlood Operations Platform &copy; {{ date('Y') }}</p>
            <p class="mt-1 text-xs">Certified Healthcare Stock Management System</p>
        </div>
    </footer>
</body>
</html>
