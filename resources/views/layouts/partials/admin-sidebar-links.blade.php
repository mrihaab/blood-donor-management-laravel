<nav class="space-y-6">
    <!-- OPERATIONS -->
    <div>
        <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Operations</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.inventory.index') }}" class="group flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.inventory.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Blood Inventory</span>
                <span class="rounded bg-red-900/50 px-1.5 py-0.5 text-[10px] font-bold text-red-300">Units</span>
            </a>
            <a href="{{ route('admin.blood_requests.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.blood_requests.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Blood Requests</span>
            </a>
            <a href="{{ route('admin.appointments.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.appointments.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Appointments</span>
            </a>
            <a href="{{ route('admin.donations.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.donations.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Donations Intake</span>
            </a>
        </div>
    </div>

    <!-- PEOPLE -->
    <div>
        <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">People & Entities</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.donors.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.donors.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Donors</span>
            </a>
            <a href="{{ route('admin.patients.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.patients.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Patients</span>
            </a>
            <a href="{{ route('admin.hospitals.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.hospitals.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Hospitals</span>
            </a>
        </div>
    </div>

    <!-- COMMUNICATION -->
    <div>
        <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Communication</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.notifications.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.notifications.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Notifications</span>
            </a>
        </div>
    </div>

    <!-- ANALYTICS -->
    <div>
        <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Analytics</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.reports.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Reports & Exports</span>
            </a>
        </div>
    </div>

    <!-- SYSTEM -->
    <div>
        <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">System</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.users.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Users & Roles</span>
            </a>
            <a href="{{ route('admin.activity-logs.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.activity-logs.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Audit Logs</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>Settings</span>
            </a>
        </div>
    </div>
</nav>
