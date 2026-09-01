<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LifeBlood Platform') }} — Operations Portal</title>

    <!-- Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full text-slate-900 antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-full">
        <!-- Mobile Sidebar Drawer Overlay -->
        <div x-show="sidebarOpen" class="fixed inset-0 z-40 flex lg:hidden" role="dialog" aria-modal="true">
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80" @click="sidebarOpen = false"></div>

            <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex w-full max-w-xs flex-1 flex-col bg-slate-900 pt-5 pb-4">
                <div class="flex items-center justify-between px-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-600 text-white font-bold">LB</div>
                        <span class="text-lg font-bold text-white tracking-tight">LifeBlood Operations</span>
                    </div>
                    <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="mt-5 h-0 flex-1 overflow-y-auto px-2 space-y-1">
                    @include('layouts.partials.admin-sidebar-links')
                </div>
            </div>
        </div>

        <!-- Static Desktop Sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col lg:bg-slate-900 lg:pt-5 lg:pb-4">
            <div class="flex items-center space-x-3 px-6 pb-4 border-b border-slate-800">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-red-700 to-red-500 text-white font-black shadow-md">LB</div>
                <div>
                    <span class="text-base font-bold text-white tracking-tight block">LifeBlood</span>
                    <span class="text-xs font-medium text-slate-400 block">Operations Platform</span>
                </div>
            </div>
            <div class="mt-4 flex flex-1 flex-col overflow-y-auto px-3">
                @include('layouts.partials.admin-sidebar-links')
            </div>
        </div>

        <!-- Main Content Shell -->
        <div class="flex flex-1 flex-col lg:pl-64">
            <!-- Top Navbar -->
            <div class="sticky top-0 z-10 flex h-16 flex-shrink-0 bg-white border-b border-slate-200 shadow-sm">
                <button @click="sidebarOpen = true" class="border-r border-slate-200 px-4 text-slate-500 lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex flex-1 justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-1 items-center">
                        <div class="w-full max-w-lg lg:max-w-xs">
                            <label for="search" class="sr-only">Search platform</label>
                            <div class="relative text-slate-400 focus-within:text-slate-600">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                                </div>
                                <input id="search" class="block w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-10 pr-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Global Search (Ctrl + K)" type="search">
                            </div>
                        </div>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6 space-x-4">
                        <!-- Dynamic Notification Bell Feed Dropdown -->
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
                        }" x-init="fetchNotifs(); setInterval(() => fetchNotifs(), 10000)">
                            <button @click="notifOpen = !notifOpen" class="relative rounded-full bg-slate-50 p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none transition">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white shadow" x-text="unreadCount"></span>
                                </template>
                            </button>

                            <div x-show="notifOpen" @click.away="notifOpen = false" class="absolute right-0 mt-2 w-80 rounded-xl bg-white p-4 shadow-xl border border-slate-200 z-50 space-y-3">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Notifications Feed</h4>
                                    <span class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full" x-text="unreadCount + ' Unread'"></span>
                                </div>
                                <div class="max-h-60 overflow-y-auto divide-y divide-slate-100">
                                    <template x-for="item in notifications" :key="item.id">
                                        <div class="py-2 space-y-1">
                                            <p class="text-xs font-bold text-slate-900" x-text="item.title"></p>
                                            <p class="text-[11px] text-slate-600" x-text="item.message"></p>
                                        </div>
                                    </template>
                                    <template x-if="notifications.length === 0">
                                        <p class="text-xs text-slate-400 italic text-center py-3">No unread notifications.</p>
                                    </template>
                                </div>
                                <div class="border-t border-slate-100 pt-2 text-center">
                                    <a href="{{ route('admin.notifications_feed.index') }}" class="text-xs font-bold text-red-600 hover:underline">View All Notifications Feed &rarr;</a>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-3 focus:outline-none">
                                <div class="h-8 w-8 rounded-full bg-red-700 text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                                </div>
                                <span class="hidden md:inline-block text-sm font-semibold text-slate-700">{{ auth()->user()->name ?? 'Admin User' }}</span>
                            </button>

                            <div x-show="userMenuOpen" @click.away="userMenuOpen = false" class="absolute right-0 mt-2 w-48 rounded-xl bg-white py-1 shadow-lg ring-1 ring-black/5 z-50">
                                <a href="{{ route('admin.2fa.show') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Two-Factor Security</a>
                                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">System Settings</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Log Out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Body Slot -->
            <main class="flex-1">
                <div class="py-8">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
