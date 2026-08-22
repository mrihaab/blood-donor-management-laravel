<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Portal') - LifeBlood Management</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900 min-h-screen flex flex-col">
    <!-- Top Navigation Bar (Single Clean Navbar without Slider matching Donor Portal style) -->
    <nav class="bg-slate-900 text-white sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Logo -->
                <div class="flex items-center space-x-3 flex-shrink-0">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5">
                        <div class="p-1.5 bg-red-600 rounded-lg text-white">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-base tracking-tight leading-none">LifeBlood</span>
                            <span class="text-[10px] text-red-400 font-bold uppercase tracking-wider bg-red-950/60 px-1.5 py-0.5 rounded border border-red-800/40">Admin</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Nav Links (Clean, No Slider) -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">Dashboard</a>
                    <a href="{{ route('admin.donors.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.donors.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">Donors</a>
                    <a href="{{ route('admin.inventory.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.inventory.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">Inventory</a>
                    <a href="{{ route('admin.donations.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.donations.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">Donations</a>
                    <a href="{{ route('admin.blood_requests.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.blood_requests.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">Requests</a>
                    <a href="{{ route('admin.appointments.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.appointments.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">Appointments</a>
                    <a href="{{ route('admin.reports.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.reports.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">Reports</a>
                    <a href="{{ route('admin.notifications.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.notifications.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">Notifications</a>
                    <a href="{{ route('admin.settings.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.settings.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">Settings</a>
                    <a href="{{ route('admin.users.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">Users</a>
                </div>

                <!-- Right Side User Menu -->
                <div class="hidden lg:flex items-center space-x-3 flex-shrink-0">
                    <span class="text-xs font-medium text-slate-300">
                        {{ Auth::user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold px-2.5 py-1 bg-slate-800 hover:bg-red-600 text-slate-200 hover:text-white rounded-lg transition border border-slate-700">
                            Log Out
                        </button>
                    </form>
                </div>

                <!-- Mobile Hamburger Toggle -->
                <div class="lg:hidden flex items-center">
                    <button id="admin-mobile-menu-btn" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="admin-mobile-menu" class="hidden lg:hidden border-t border-slate-800 bg-slate-900 px-4 pt-2 pb-4 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                Dashboard
            </a>
            <a href="{{ route('admin.donors.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.donors.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                Donors
            </a>
            <a href="{{ route('admin.inventory.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.inventory.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                Blood Inventory
            </a>
            <a href="{{ route('admin.donations.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.donations.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                Donations
            </a>
            <a href="{{ route('admin.blood_requests.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.blood_requests.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                Blood Requests
            </a>
            <a href="{{ route('admin.appointments.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.appointments.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                Appointments
            </a>
            <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                Reports
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.notifications.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                Notifications
            </a>
            <a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.settings.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                Settings
            </a>
            <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                User Management
            </a>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                <span class="text-sm font-medium text-slate-300">{{ Auth::user()->name }} (Admin)</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold px-3 py-1.5 bg-red-600 text-white rounded-lg">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Page Title Header -->
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-800">@yield('page_title', 'Admin Dashboard')</h1>
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
        @if (session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
                <span class="font-bold">Success!</span> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
                <span class="font-bold">Error!</span> {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- Main Content Body -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <script>
        document.getElementById('admin-mobile-menu-btn')?.addEventListener('click', function() {
            document.getElementById('admin-mobile-menu')?.classList.toggle('hidden');
        });
    </script>
</body>
</html>
