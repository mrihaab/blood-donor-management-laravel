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
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar -->
        <aside class="w-full lg:w-64 bg-slate-900 text-white flex-shrink-0">
            <div class="p-4 flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-red-600 rounded-lg text-white">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-lg tracking-wide block">LifeBlood</span>
                        <span class="text-xs text-red-400 font-medium">ADMIN PORTAL</span>
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-1 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2.5 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📊</span> Dashboard
                </a>

                <a href="{{ route('admin.donors.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition {{ request()->routeIs('admin.donors.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">🧍</span> Donors
                </a>

                <a href="{{ route('admin.inventory.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition {{ request()->routeIs('admin.inventory.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">🗂️</span> Blood Inventory
                </a>

                <a href="{{ route('admin.donations.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition {{ request()->routeIs('admin.donations.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">💉</span> Donations
                </a>

                <a href="{{ route('admin.blood_requests.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition {{ request()->routeIs('admin.blood_requests.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📥</span> Blood Requests
                </a>

                <a href="{{ route('admin.appointments.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition {{ request()->routeIs('admin.appointments.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📅</span> Appointments
                </a>

                <a href="{{ route('admin.reports.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition {{ request()->routeIs('admin.reports.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📄</span> Reports
                </a>

                <a href="{{ route('admin.notifications.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition {{ request()->routeIs('admin.notifications.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">🔔</span> Notifications
                </a>

                <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition {{ request()->routeIs('admin.settings.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">⚙️</span> Settings
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">👥</span> User Management
                </a>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header Bar -->
            <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h1 class="text-xl font-bold text-gray-800">@yield('page_title', 'Admin Dashboard')</h1>

                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-700">{{ Auth::user()->name }} (Admin)</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition">
                            Log Out
                        </button>
                    </form>
                </div>
            </header>

            <!-- Flash Notifications -->
            <div class="px-6 pt-4">
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

            <!-- Page Body -->
            <main class="flex-1 p-6 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
