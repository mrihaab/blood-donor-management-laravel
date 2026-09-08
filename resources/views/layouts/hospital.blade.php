<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ darkMode: localStorage.getItem('theme') !== 'light', sidebarOpen: false }" x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Hospital Command Center') — {{ config('app.name', 'LifeBlood Platform') }}</title>

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
    <meta name="apple-mobile-web-app-title" content="MediSync Command">
    <link rel="apple-touch-icon" href="/images/icon-192.png">
    <meta name="theme-color" content="#0b1329">

    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* Instant Touch Active Scaling Feedback */
        button, a, input[type="submit"] {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        button:active, a.btn-active:active {
            transform: scale(0.96);
            transition: transform 0.08s ease-out;
        }

        /* Top Progress Bar on Page Shift */
        #top-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #2563eb, #3b82f6, #06b6d4);
            z-index: 99999;
            transition: width 0.2s ease-out, opacity 0.3s ease;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.8);
        }

        /* Dynamic Theme Color-Scheme & Scrollbar Controls */
        html.dark {
            color-scheme: dark;
            scrollbar-color: #334155 #090d16;
            scrollbar-width: thin;
        }
        html.dark ::-webkit-scrollbar {
            width: 9px;
            height: 9px;
        }
        html.dark ::-webkit-scrollbar-track {
            background: #090d16;
        }
        html.dark ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 9999px;
            border: 2px solid #090d16;
        }
        html.dark ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }

        html:not(.dark) {
            color-scheme: light;
            scrollbar-color: #cbd5e1 #f8fafc;
            scrollbar-width: thin;
        }
        html:not(.dark) ::-webkit-scrollbar {
            width: 9px;
            height: 9px;
        }
        html:not(.dark) ::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        html:not(.dark) ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
            border: 2px solid #f8fafc;
        }
        html:not(.dark) ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Strict Desktop Safeguard for Mobile Bottom Nav */
        @media (min-width: 1024px) {
            .mobile-bottom-nav {
                display: none !important;
            }
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-50 text-slate-900 dark:bg-[#090d16] dark:text-slate-100 transition-colors duration-200">
    <div class="min-h-screen flex flex-col bg-slate-50 dark:bg-[#090d16]">

        <!-- Universal Off-Canvas Slide-Over Navigation Drawer (Mobile & Desktop) -->
        <div x-show="sidebarOpen" class="fixed inset-0 z-50 flex" role="dialog" aria-modal="true" style="display: none;">
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition-opacity ease-linear duration-300" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" 
                 @click="sidebarOpen = false"></div>

            <div x-show="sidebarOpen" 
                 x-transition:enter="transition ease-in-out duration-300 transform" 
                 x-transition:enter-start="-translate-x-full" 
                 x-transition:enter-end="translate-x-0" 
                 x-transition:leave="transition ease-in-out duration-300 transform" 
                 x-transition:leave-start="translate-x-0" 
                 x-transition:leave-end="-translate-x-full" 
                 class="relative flex w-full max-w-xs flex-1 flex-col justify-between bg-white dark:bg-[#0b1329] border-r border-slate-200 dark:border-slate-800 pt-5 pb-20 max-h-screen overflow-y-auto shadow-2xl z-10">
                <div>
                    <div class="flex items-center justify-between px-5 pb-4 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-500 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-blue-500/20">
                                🏥
                            </div>
                            <div>
                                <span class="font-extrabold text-base text-slate-900 dark:text-white tracking-tight block">MediSync</span>
                                <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider block">Hospital Operations</span>
                            </div>
                        </div>
                        <button @click="sidebarOpen = false" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="mt-4 px-3 space-y-1.5">
                        @include('layouts.partials.hospital-sidebar-links')
                    </div>
                </div>

                <!-- Drawer Profile & Sign Out Footer -->
                <div class="p-4 mx-3 my-4 bg-slate-100 dark:bg-slate-900/90 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="h-9 w-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-extrabold text-sm shadow">
                            {{ strtoupper(substr(auth()->user()->name ?? 'H', 0, 2)) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">{{ auth()->user()->name ?? 'Hospital Staff' }}</p>
                            <p class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold truncate">{{ auth()->user()->hospital->name ?? 'Clinical Partner' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-3 text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white rounded-xl transition flex items-center justify-center gap-1.5 border border-rose-200 dark:border-rose-800/80 shadow-md">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Full-Width Main Shell (100% Full Screen Width on Desktop) -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- Top Sticky Header Bar (Full-Width Header-Nav Layout matching reference) -->
            <header class="sticky top-0 z-40 bg-white/90 dark:bg-[#0b1329]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800/80 px-4 sm:px-8 py-3.5 flex items-center justify-between shadow-sm dark:shadow-xl transition-colors">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <!-- 3-Bars Drawer Toggle Button (Far Left) -->
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white p-2 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 transition active:scale-95" title="Toggle Navigation Menu">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <!-- Brand / Portal Logo & Title -->
                    <div class="flex items-center space-x-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-500 text-white font-black text-sm shadow-md shadow-blue-500/20">🏥</div>
                        <div class="flex items-center gap-2">
                            <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">MediSync</span>
                            <span class="hidden sm:inline-block px-2 py-0.5 text-[10px] font-black uppercase tracking-wider bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 rounded-md border border-blue-200 dark:border-blue-800">Hospital</span>
                        </div>
                    </div>

                    <!-- Page Subtitle separator -->
                    <div class="hidden md:flex items-center border-l border-slate-200 dark:border-slate-800 pl-3.5 ml-1">
                        <h1 class="text-sm font-bold text-slate-600 dark:text-slate-300">@yield('page_title', 'MediSync Command Center')</h1>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Dynamic 1-Click Light/Dark Mode Toggle Button -->
                    <button @click="darkMode = !darkMode" class="relative rounded-xl bg-slate-100 dark:bg-slate-900 p-2 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 border border-slate-300 dark:border-slate-800 focus:outline-none transition flex items-center justify-center shadow-sm" title="Toggle Light/Dark Theme">
                        <template x-if="darkMode">
                            <span class="text-sm font-bold flex items-center gap-1">☀️ <span class="hidden md:inline text-[11px]">Light</span></span>
                        </template>
                        <template x-if="!darkMode">
                            <span class="text-sm font-bold flex items-center gap-1">🌙 <span class="hidden md:inline text-[11px]">Dark</span></span>
                        </template>
                    </button>

                    <!-- Dynamic Live Notification Bell Dropdown Flyout -->
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
                        },
                        markAllRead() {
                            this.unreadCount = 0;
                            this.notifications = [];
                            fetch('{{ route('notifications.mark_all_read') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            }).catch(() => {});
                        }
                    }" x-init="fetchNotifs(); setInterval(() => fetchNotifs(), 5000)">
                        <button @click="notifOpen = !notifOpen" class="relative rounded-xl bg-slate-100 dark:bg-slate-900 p-2 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white border border-slate-300 dark:border-slate-800 focus:outline-none transition shadow-sm">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <template x-if="unreadCount > 0">
                                <span class="absolute -top-1 -right-1 flex h-4.5 w-4.5 items-center justify-center rounded-full bg-blue-600 text-[10px] font-black text-white shadow-lg ring-2 ring-white dark:ring-slate-900" x-text="unreadCount"></span>
                            </template>
                        </button>

                        <div x-show="notifOpen" @click.away="notifOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute right-0 mt-2.5 w-88 sm:w-96 rounded-2xl bg-white dark:bg-[#0c1427] p-4 shadow-2xl border border-slate-200 dark:border-slate-800/80 z-50 space-y-3" style="display: none;">
                            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-2.5">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Notifications</h4>
                                    <span class="text-[10px] font-extrabold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/80 border border-blue-200 dark:border-blue-800 px-2 py-0.5 rounded-full" x-text="unreadCount + ' Unread'"></span>
                                </div>
                                <button @click.prevent="markAllRead()" type="button" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline flex items-center gap-1">
                                    <span>✓ Mark All Read</span>
                                </button>
                            </div>

                            <div class="max-h-72 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                                <template x-for="item in notifications" :key="item.id">
                                    <a :href="'/notifications/' + item.id + '/click'" class="block p-3 rounded-xl bg-slate-50 dark:bg-[#111c38] hover:bg-slate-100 dark:hover:bg-[#162447] border border-slate-200/80 dark:border-slate-800 transition group text-left space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                Clinical Alert
                                            </span>
                                            <span class="text-[10px] text-slate-600 dark:text-slate-400 font-semibold" x-text="item.created_at || 'Just now'"></span>
                                        </div>
                                        <p class="text-xs font-bold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition" x-text="item.title"></p>
                                        <p class="text-[11px] text-slate-700 dark:text-slate-300 line-clamp-2 leading-relaxed" x-text="item.message"></p>
                                    </a>
                                </template>
                                <template x-if="notifications.length === 0">
                                    <div class="py-8 text-center space-y-2">
                                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 text-lg">
                                            ✓
                                        </div>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">All caught up!</p>
                                        <p class="text-[11px] text-slate-600 dark:text-slate-400">No unread notifications at this time.</p>
                                    </div>
                                </template>
                            </div>

                            <div class="border-t border-slate-200 dark:border-slate-800/80 pt-2.5 text-center">
                                <a href="{{ route('hospital.notifications.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition">View All Notifications &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- Primary Action Button -->
                    <a href="{{ route('hospital.requests.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-bold rounded-xl text-xs transition shadow-lg shadow-blue-600/30 flex items-center gap-1.5">
                        <span>+</span>
                        <span class="hidden sm:inline">New Requisition</span>
                    </a>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-8 pb-32 lg:pb-12">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/80 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 text-sm rounded-r-2xl shadow-md">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/80 border-l-4 border-rose-500 text-rose-800 dark:text-rose-300 text-sm rounded-r-2xl shadow-md">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Native Mobile App Bottom Navigation Bar (Hidden on Desktop & Tablet) -->
    <nav class="mobile-bottom-nav md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 dark:bg-[#0b1329]/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800/90 px-3 py-2 text-slate-900 dark:text-white shadow-2xl flex items-center justify-around">
        <a href="{{ route('hospital.dashboard') }}" class="flex flex-col items-center gap-0.5 text-center text-[10px] font-bold {{ request()->routeIs('hospital.dashboard') ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <span class="text-lg">🏠</span>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('hospital.requests.create') }}" class="flex flex-col items-center gap-0.5 text-center text-[10px] font-bold {{ request()->routeIs('hospital.requests.create') ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <span class="text-lg">➕</span>
            <span>New Req</span>
        </a>
        <a href="{{ route('hospital.requests.index') }}" class="flex flex-col items-center gap-0.5 text-center text-[10px] font-bold {{ request()->routeIs('hospital.requests.index') ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <span class="text-lg">📋</span>
            <span>Req Log</span>
        </a>
        <a href="{{ route('hospital.patients.index') }}" class="flex flex-col items-center gap-0.5 text-center text-[10px] font-bold {{ request()->routeIs('hospital.patients.*') ? 'text-blue-600 dark:text-blue-400 font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}">
            <span class="text-lg">👥</span>
            <span>Patients</span>
        </a>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bar = document.createElement('div');
            bar.id = 'top-loading-bar';
            document.body.appendChild(bar);

            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    const href = this.getAttribute('href');
                    if (href && !href.startsWith('#') && !href.startsWith('javascript') && this.target !== '_blank') {
                        bar.style.width = '75%';
                        bar.style.opacity = '1';
                    }
                });
            });
            window.addEventListener('beforeunload', () => {
                bar.style.width = '100%';
            });

            // Auto CSRF Keep-Alive Heartbeat every 10 mins to prevent 419 Page Expired errors
            setInterval(() => {
                fetch('/dashboard', { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(() => {});
            }, 600000);
        });
    </script>
</body>
</html>
