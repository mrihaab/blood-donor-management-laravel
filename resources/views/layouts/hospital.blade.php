<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Hospital Portal') - {{ config('app.name', 'Blood Bank Operations Platform') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
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
                <div class="flex items-center space-x-3">
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
