<nav class="space-y-6">
    <!-- OPERATIONS -->
    <div>
        <p class="px-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Operations</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.dashboard') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.inventory.index') }}" 
               class="group flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.inventory.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Blood Inventory</span>
                </div>
                <span class="rounded bg-rose-100 dark:bg-rose-900/50 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 dark:text-rose-300">Units</span>
            </a>
            <a href="{{ route('admin.blood_requests.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.blood_requests.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Blood Requests</span>
            </a>
            <a href="{{ route('admin.appointments.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.appointments.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Appointments</span>
            </a>
            <a href="{{ route('admin.donations.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.donations.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                <span>Donations Intake</span>
            </a>
        </div>
    </div>

    <!-- PEOPLE -->
    <div>
        <p class="px-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">People & Entities</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.donors.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.donors.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Donors</span>
            </a>
            <a href="{{ route('admin.patients.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.patients.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Patients</span>
            </a>
            <a href="{{ route('admin.hospitals.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.hospitals.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0v-4a1 1 0 011-1h2a1 1 0 011 1v4m-4 0h4"/></svg>
                <span>Hospitals</span>
            </a>
        </div>
    </div>

    <!-- COMMUNICATION -->
    <div>
        <p class="px-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Communication</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.notifications.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.notifications.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Notifications</span>
            </a>
        </div>
    </div>

    <!-- ANALYTICS -->
    <div>
        <p class="px-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Analytics</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.reports.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.reports.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Reports & Exports</span>
            </a>
        </div>
    </div>

    <!-- SYSTEM -->
    <div>
        <p class="px-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">System</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.users.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Users & Roles</span>
            </a>
            <a href="{{ route('admin.activity-logs.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.activity-logs.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Audit Logs</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" 
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.settings.*') ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Settings</span>
            </a>
        </div>
    </div>
</nav>
