<nav class="space-y-6">
    <!-- CLINICAL OPERATIONS -->
    <div>
        <p class="px-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Clinical Operations</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('hospital.dashboard') }}" class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('hospital.dashboard') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('hospital.requests.index') }}" class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('hospital.requests.index') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Blood Requisitions</span>
            </a>
            <a href="{{ route('hospital.requests.create') }}" class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('hospital.requests.create') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Requisition</span>
            </a>
        </div>
    </div>

    <!-- PATIENT CARE -->
    <div>
        <p class="px-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Patient Care</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('hospital.patients.index') }}" class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('hospital.patients.index') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Patient Directory</span>
            </a>
            <a href="{{ route('hospital.patients.create') }}" class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('hospital.patients.create') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Register Patient</span>
            </a>
        </div>
    </div>

    <!-- TRANSFUSIONS & SAFETY -->
    <div>
        <p class="px-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Transfusions & Safety</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('hospital.transfusions.index') }}" class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('hospital.transfusions.index') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.605 15.12a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span>Transfusions Audit</span>
            </a>
            <a href="{{ route('hospital.transfusions.create') }}" class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('hospital.transfusions.create') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Record Outcome</span>
            </a>
        </div>
    </div>

    <!-- COMMUNICATION -->
    <div>
        <p class="px-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Communication</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('hospital.notifications.index') }}" class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('hospital.notifications.*') ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Notifications Feed</span>
            </a>
        </div>
    </div>
</nav>
