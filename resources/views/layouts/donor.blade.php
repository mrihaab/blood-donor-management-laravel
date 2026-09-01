<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Donor Portal') - LifeBlood Management</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900 min-h-screen flex flex-col">
    <!-- Top Horizontal Navigation Bar -->
    <nav class="bg-slate-900 text-white sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Logo -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('donor.dashboard') }}" class="flex items-center space-x-3 flex-shrink-0">
                        <div class="p-2 bg-red-600 rounded-lg text-white">
                            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-lg tracking-wide block leading-tight">LifeBlood</span>
                            <span class="text-[10px] text-red-400 font-bold uppercase tracking-wider block">Donor Portal</span>
                        </div>
                    </a>

                    <!-- Desktop Nav Links -->
                    <div class="hidden md:flex items-center space-x-1">
                        <a href="{{ route('donor.dashboard') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2 {{ request()->routeIs('donor.dashboard') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>🩸</span>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('donor.profile.edit') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2 {{ request()->routeIs('donor.profile.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>👤</span>
                            <span>My Profile</span>
                        </a>

                        <a href="{{ route('donor.appointments.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2 {{ request()->routeIs('donor.appointments.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>📅</span>
                            <span>Book Appointment</span>
                        </a>

                        <a href="{{ route('donor.history') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2 {{ request()->routeIs('donor.history') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>📜</span>
                            <span>Donation History</span>
                        </a>

                        <a href="{{ route('donor.blood_requests.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition flex items-center space-x-2 {{ request()->routeIs('donor.blood_requests.*') ? 'bg-red-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>📥</span>
                            <span>Request Blood</span>
                        </a>
                    </div>
                </div>

                <!-- Right Side User Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Dynamic Live Notification Bell Dropdown -->
                    <div class="relative" x-data="{ 
                        notifOpen: false, 
                        unreadCount: 0, 
                        notifications: [],
                        fetchNotifs() {
                            fetch('{{ route('notifications.unread_feed') }}')
                                .then(res => res.json())
                                .then(data => {
                                    this.unreadCount = data.unreadCount;
                                    this.notifications = data.notifications;
                                })
                                .catch(() => {});
                        }
                    }" x-init="fetchNotifs(); setInterval(() => fetchNotifs(), 5000)">
                        <button @click="notifOpen = !notifOpen" class="relative rounded-full bg-slate-800 p-2 text-slate-300 hover:bg-slate-700 hover:text-white focus:outline-none transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <template x-if="unreadCount > 0">
                                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white shadow" x-text="unreadCount"></span>
                            </template>
                        </button>

                        <div x-show="notifOpen" @click.away="notifOpen = false" class="absolute right-0 mt-2 w-84 rounded-xl bg-white p-4 shadow-xl border border-slate-200 z-50 space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Notifications Feed</h4>
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('notifications.mark_all_read') }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[10px] font-bold text-slate-500 hover:text-red-600 underline">✓ Mark All Read</button>
                                    </form>
                                    <span class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full" x-text="unreadCount + ' Unread'"></span>
                                </div>
                            </div>
                            <div class="max-h-60 overflow-y-auto divide-y divide-slate-100">
                                <template x-for="item in notifications" :key="item.id">
                                    <a :href="'/notifications/' + item.id + '/click'" class="block py-2 space-y-1 hover:bg-slate-50 rounded-lg px-2 transition group text-left">
                                        <p class="text-xs font-bold text-slate-900 group-hover:text-red-600" x-text="item.title"></p>
                                        <p class="text-[11px] text-slate-600" x-text="item.message"></p>
                                    </a>
                                </template>
                                <template x-if="notifications.length === 0">
                                    <p class="text-xs text-slate-400 italic text-center py-3">No unread notifications.</p>
                                </template>
                            </div>
                            <div class="border-t border-slate-100 pt-2 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-red-600 hover:underline">View All Notifications &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <span class="text-sm font-medium text-slate-300">
                        {{ Auth::user()->name }} <span class="text-xs text-red-400 bg-red-950/60 px-2 py-0.5 rounded border border-red-800/50 font-bold">Donor</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 bg-slate-800 hover:bg-red-600 text-slate-200 hover:text-white rounded-lg transition border border-slate-700">
                            Log Out
                        </button>
                    </form>
                </div>

                <!-- Mobile Hamburger Toggle -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-800 bg-slate-900 px-4 pt-2 pb-4 space-y-1">
            <a href="{{ route('donor.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('donor.dashboard') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                🩸 Dashboard
            </a>
            <a href="{{ route('donor.profile.edit') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('donor.profile.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                👤 My Profile
            </a>
            <a href="{{ route('donor.appointments.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('donor.appointments.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                📅 Book Appointment
            </a>
            <a href="{{ route('donor.history') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('donor.history') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                📜 Donation History
            </a>
            <a href="{{ route('donor.blood_requests.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('donor.blood_requests.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                📥 Request Blood
            </a>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                <span class="text-sm font-medium text-slate-300">{{ Auth::user()->name }}</span>
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
            <h1 class="text-xl font-bold text-gray-800">@yield('page_title', 'Donor Dashboard')</h1>
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
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>
</body>
</html>
