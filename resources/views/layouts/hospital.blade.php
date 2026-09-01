<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Hospital Portal') - {{ config('app.name', 'Blood Bank Operations Platform') }}</title>

    <!-- Fonts & Tailwind & Alpine.js -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900 bg-gray-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex bg-gray-50">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0">
            <div class="p-6 border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                        H
                    </div>
                    <div>
                        <span class="font-bold text-lg text-white tracking-wide">Hospital Portal</span>
                        <p class="text-xs text-slate-400 truncate max-w-[140px]">{{ auth()->user()->hospital->name ?? 'Clinical Operations' }}</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1">
                <a href="{{ route('hospital.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('hospital.dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    Dashboard
                </a>
                <a href="{{ route('hospital.requests.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('hospital.requests.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    Blood Requisitions
                </a>
                <a href="{{ route('hospital.patients.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('hospital.patients.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    Patient Directory
                </a>
                <a href="{{ route('notifications.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('notifications.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                    Notifications Feed
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span>Logged in as:</span>
                    <span class="font-semibold text-slate-200">{{ auth()->user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 text-xs font-semibold text-center text-red-400 bg-slate-800 rounded-lg hover:bg-red-600 hover:text-white transition">
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">@yield('page_title', 'Hospital Dashboard')</h1>
                </div>
                <div class="flex items-center space-x-4">
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
                        <button @click="notifOpen = !notifOpen" class="relative rounded-full bg-slate-100 p-2 text-slate-600 hover:bg-slate-200 hover:text-slate-900 focus:outline-none transition">
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
                                        <button type="submit" class="text-[10px] font-bold text-slate-500 hover:text-blue-600 underline">✓ Mark All Read</button>
                                    </form>
                                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full" x-text="unreadCount + ' Unread'"></span>
                                </div>
                            </div>
                            <div class="max-h-60 overflow-y-auto divide-y divide-slate-100">
                                <template x-for="item in notifications" :key="item.id">
                                    <a :href="'/notifications/' + item.id + '/click'" class="block py-2 space-y-1 hover:bg-slate-50 rounded-lg px-2 transition group text-left">
                                        <p class="text-xs font-bold text-slate-900 group-hover:text-blue-600" x-text="item.title"></p>
                                        <p class="text-[11px] text-slate-600" x-text="item.message"></p>
                                    </a>
                                </template>
                                <template x-if="notifications.length === 0">
                                    <p class="text-xs text-slate-400 italic text-center py-3">No unread notifications.</p>
                                </template>
                            </div>
                            <div class="border-t border-slate-100 pt-2 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-blue-600 hover:underline">View All Notifications &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full border border-blue-200">
                        {{ auth()->user()->hospital->name ?? 'Clinical Partner' }}
                    </span>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm rounded-r-lg shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded-r-lg shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
