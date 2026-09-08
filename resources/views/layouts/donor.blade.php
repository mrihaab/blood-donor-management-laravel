<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ darkMode: localStorage.getItem('theme') !== 'light', mobileMenuOpen: false }" x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Donor Portal') — {{ config('app.name', 'LifeBlood Platform') }}</title>

    <!-- Fonts & Tailwind & Alpine.js -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        if (localStorage.getItem('theme') !== 'light') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- PWA & Mobile App Native Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0b1329">

    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }

        button, a, input[type="submit"] {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        /* Dynamic Theme Scrollbars */
        html.dark {
            color-scheme: dark;
            scrollbar-color: #334155 #090d16;
            scrollbar-width: thin;
        }
        html.dark ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        html.dark ::-webkit-scrollbar-track {
            background: #090d16;
        }
        html.dark ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 9999px;
        }

        html:not(.dark) {
            color-scheme: light;
            scrollbar-color: #cbd5e1 #f8fafc;
            scrollbar-width: thin;
        }
        html:not(.dark) ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        html:not(.dark) ::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        html:not(.dark) ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-[#090d16] text-slate-900 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-200">

    <!-- Top Navigation Header -->
    <header class="bg-white dark:bg-[#0c1427] border-b border-slate-200 dark:border-slate-800/80 sticky top-0 z-50 shadow-sm transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Left: Brand Logo & Title -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('donor.dashboard') }}" class="flex items-center space-x-3 group">
                        <div class="p-2 bg-gradient-to-tr from-rose-600 to-red-500 text-white rounded-xl shadow-md shadow-rose-950/20 group-hover:scale-105 transition">
                            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-black text-lg tracking-tight block leading-tight text-slate-900 dark:text-white">LifeBlood</span>
                            <span class="text-[10px] text-rose-600 dark:text-rose-400 font-extrabold uppercase tracking-wider block">Donor Portal</span>
                        </div>
                    </a>

                    <!-- Desktop Nav Links -->
                    <nav class="hidden md:flex items-center space-x-1" aria-label="Donor Navigation">
                        <a href="{{ route('donor.dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs md:text-sm font-bold transition flex items-center gap-2 {{ request()->routeIs('donor.dashboard') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('donor.appointments.index') }}" class="px-3.5 py-2 rounded-xl text-xs md:text-sm font-bold transition flex items-center gap-2 {{ request()->routeIs('donor.appointments.*') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Appointments</span>
                        </a>

                        <a href="{{ route('donor.history') }}" class="px-3.5 py-2 rounded-xl text-xs md:text-sm font-bold transition flex items-center gap-2 {{ request()->routeIs('donor.history') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Donation History</span>
                        </a>

                        <a href="{{ route('donor.blood_requests.index') }}" class="px-3.5 py-2 rounded-xl text-xs md:text-sm font-bold transition flex items-center gap-2 {{ request()->routeIs('donor.blood_requests.*') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Blood Requests</span>
                        </a>

                        <a href="{{ route('donor.profile.edit') }}" class="px-3.5 py-2 rounded-xl text-xs md:text-sm font-bold transition flex items-center gap-2 {{ request()->routeIs('donor.profile.*') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>My Profile</span>
                        </a>
                    </nav>
                </div>

                <!-- Right Side Actions: Theme Toggle, Notifications, User Menu -->
                <div class="hidden md:flex items-center space-x-3">
                    <!-- Dark Mode Toggle Button -->
                    <button @click="darkMode = !darkMode" class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition" aria-label="Toggle Theme">
                        <svg x-show="!darkMode" class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg x-show="darkMode" class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div class="relative" x-data="{ 
                        notifOpen: false, 
                        unreadCount: 0, 
                        notifications: [],
                        fetchNotifs() {
                            fetch('{{ route('notifications.unread_feed') }}')
                                .then(res => res.json())
                                .then(data => {
                                    this.unreadCount = data.unreadCount || 0;
                                    this.notifications = data.notifications || [];
                                })
                                .catch(() => {});
                        }
                    }" x-init="fetchNotifs(); setInterval(() => fetchNotifs(), 10000)">
                        <button @click="notifOpen = !notifOpen" class="relative p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition" aria-label="Notifications Feed">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <template x-if="unreadCount > 0">
                                <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-600 text-[10px] font-bold text-white shadow" x-text="unreadCount"></span>
                            </template>
                        </button>

                        <div x-show="notifOpen" @click.away="notifOpen = false" x-transition class="absolute right-0 mt-2 w-80 rounded-2xl bg-white dark:bg-[#0c1427] p-4 shadow-2xl border border-slate-200 dark:border-slate-800 z-50 space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Notifications Feed</h4>
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('notifications.mark_all_read') }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 underline">✓ Mark All Read</button>
                                    </form>
                                    <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/80 px-2 py-0.5 rounded-full" x-text="unreadCount + ' Unread'"></span>
                                </div>
                            </div>
                            <div class="max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60">
                                <template x-for="item in notifications" :key="item.id">
                                    <a :href="'/notifications/' + item.id + '/click'" class="block py-2 space-y-1 hover:bg-slate-50 dark:hover:bg-slate-800/40 rounded-lg px-2 transition group text-left">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400" x-text="item.title"></p>
                                        <p class="text-[11px] text-slate-600 dark:text-slate-400" x-text="item.message"></p>
                                    </a>
                                </template>
                                <template x-if="notifications.length === 0">
                                    <p class="text-xs text-slate-400 italic text-center py-4">You're all caught up! No unread notifications.</p>
                                </template>
                            </div>
                            <div class="border-t border-slate-100 dark:border-slate-800 pt-2 text-center">
                                <a href="{{ route('donor.notifications.index') }}" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline">View All Notifications &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Badge & Logout -->
                    <div class="flex items-center space-x-3 pl-2 border-l border-slate-200 dark:border-slate-800">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                            {{ Auth::user()->name }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs font-bold px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-rose-600 hover:text-white text-slate-700 dark:text-slate-300 rounded-xl transition border border-slate-200 dark:border-slate-700">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Hamburger Menu Button -->
                <div class="md:hidden flex items-center space-x-2">
                    <!-- Mobile Theme Toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition" aria-label="Toggle Theme">
                        <svg x-show="!darkMode" class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg x-show="darkMode" class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </button>

                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition" aria-label="Toggle Navigation Menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] px-4 pt-3 pb-6 space-y-2">
            <a href="{{ route('donor.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('donor.dashboard') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span>🩸</span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('donor.appointments.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('donor.appointments.*') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span>📅</span>
                <span>Appointments</span>
            </a>
            <a href="{{ route('donor.history') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('donor.history') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span>📜</span>
                <span>Donation History</span>
            </a>
            <a href="{{ route('donor.blood_requests.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('donor.blood_requests.*') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span>📥</span>
                <span>Blood Requests</span>
            </a>
            <a href="{{ route('donor.notifications.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('donor.notifications.*') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span>🔔</span>
                <span>Notifications</span>
            </a>
            <a href="{{ route('donor.profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('donor.profile.*') ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span>👤</span>
                <span>My Profile</span>
            </a>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-bold px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-sm">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Global Flash Notifications -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
        @if (session('success'))
            <div class="p-4 mb-4 text-xs md:text-sm text-emerald-800 dark:text-emerald-300 rounded-2xl bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2 shadow-sm" role="alert">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span><strong class="font-bold">Success:</strong> {{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 mb-4 text-xs md:text-sm text-rose-800 dark:text-rose-300 rounded-2xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 flex items-center gap-2 shadow-sm" role="alert">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><strong class="font-bold">Error:</strong> {{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer Landmark -->
    <footer class="bg-white dark:bg-[#0c1427] border-t border-slate-200 dark:border-slate-800 py-6 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400 font-medium">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>LifeBlood Donor Network — Lifesaving Community Platform</span>
            </div>
            <div>
                <span>&copy; {{ date('Y') }} LifeBlood. All rights reserved.</span>
            </div>
        </div>
    </footer>
</body>
</html>
