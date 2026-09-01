@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Blood Bank Operations Center</h1>
            <p class="text-sm text-slate-500">Real-time inventory overview, critical requisition requests, and active donor statistics.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.donations.create') }}" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                + New Intake Donation
            </a>
            <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                View Barcode Bags
            </a>
        </div>
    </div>

    <!-- Active Flashing Emergency Command Banner -->
    @if(isset($activeEmergencyRequests) && $activeEmergencyRequests->count() > 0)
        <div class="rounded-2xl border-2 border-red-600 bg-gradient-to-r from-red-600 via-rose-600 to-red-700 p-6 text-white shadow-xl space-y-4">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-red-500/60 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center text-white text-xl animate-bounce">
                        🚨
                    </div>
                    <div>
                        <h3 class="text-lg font-black tracking-wide uppercase">ACTIVE CRITICAL EMERGENCY REQUISITIONS ({{ $activeEmergencyRequests->count() }})</h3>
                        <p class="text-xs text-red-100 font-medium">Urgent clinical requisitions requiring immediate central bank dispense or donor broadcast dispatch.</p>
                    </div>
                </div>
                <a href="{{ route('admin.emergency_requests.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-red-700 font-bold text-xs rounded-xl shadow hover:bg-red-50 transition">
                    Open Emergency Control Room &rarr;
                </a>
            </div>

            <div class="divide-y divide-red-500/40">
                @foreach($activeEmergencyRequests as $req)
                    <div class="py-3 flex flex-col lg:flex-row lg:items-center justify-between gap-3 text-sm">
                        <div class="space-y-0.5">
                            <div class="flex items-center space-x-2">
                                <span class="font-black text-white bg-black/30 px-2 py-0.5 rounded text-xs">#REQ-{{ $req->id }}</span>
                                <span class="font-bold text-yellow-300 text-base">{{ $req->hospital ?? 'Hospital' }} ({{ $req->city ?? 'Metropolis' }})</span>
                                <span class="text-xs font-black bg-white text-red-700 px-2 py-0.5 rounded-full">{{ $req->units_needed }} Unit(s) of {{ $req->bloodGroup->name ?? 'N/A' }}</span>
                            </div>
                            <p class="text-xs text-red-100">Patient: {{ $req->patient_name ?? 'N/A' }} | Required for: {{ $req->reason ?? 'Emergency Medical Requisition' }}</p>
                        </div>

                        <div class="flex items-center space-x-3">
                            @if($req->has_enough_stock)
                                <span class="text-xs font-bold bg-emerald-500 text-white px-3 py-1.5 rounded-lg flex items-center gap-1 shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Bank Stock Available ({{ $req->matching_stock_count }} Units)
                                </span>
                                <form method="POST" action="{{ route('admin.blood_requests.instant_dispense', $req->id) }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-1.5 bg-emerald-400 hover:bg-emerald-300 text-slate-900 font-extrabold text-xs rounded-lg shadow transition">
                                        ⚡ 1-Click Instant Dispense
                                    </button>
                                </form>
                            @else
                                <span class="text-xs font-bold bg-amber-400 text-slate-950 px-3 py-1.5 rounded-lg flex items-center gap-1 shadow">
                                    ⚠️ Stock Depleted ({{ $req->matching_stock_count }} Units)
                                </span>
                                <form method="POST" action="{{ route('admin.blood_requests.notify_donors', $req->id) }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-1.5 bg-yellow-300 hover:bg-yellow-200 text-slate-950 font-extrabold text-xs rounded-lg shadow transition">
                                        📲 Dispatch Emergency Donors (City Match)
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Top Operational KPI Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card title="Available Blood Units" value="{{ $totalAvailableUnits }}" icon="droplet" color="red" subtext="Derived from active BloodUnit bags" />
        <x-stat-card title="Pending Requisitions" value="{{ $pendingRequests }}" icon="alert-triangle" color="amber" subtext="Requires administrative review" />
        <x-stat-card title="Total Active Donors" value="{{ $activeDonors }}" icon="users" color="blue" subtext="Registered eligible donors" />
        <x-stat-card title="Expiring (Next 7 Days)" value="{{ $expiringSoonCount }}" icon="clock" color="slate" subtext="Requires inventory priority" />
    </div>

    <!-- Blood Group Stock Matrix -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Blood Stock Availability Grid</h2>
                <p class="text-xs text-slate-500">Live inventory bag counts calculated directly from barcode unit records.</p>
            </div>
            <a href="{{ route('admin.inventory.index') }}" class="text-xs font-semibold text-red-600 hover:text-red-700">Manage Units &rarr;</a>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8">
            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $groupName)
                @php
                    $groupStock = $bloodInventory->firstWhere('blood_group', $groupName);
                    $count = $groupStock['units_available'] ?? 0;
                    $status = $count < 5 ? 'critical' : ($count < 10 ? 'low' : 'healthy');
                    $badgeColor = match($status) {
                        'critical' => 'bg-rose-50 text-rose-700 border-rose-200',
                        'low' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'healthy' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    };
                @endphp
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-center">
                    <span class="inline-block rounded-lg bg-red-700 px-3 py-1 text-sm font-black text-white shadow-sm">{{ $groupName }}</span>
                    <div class="mt-3">
                        <span class="text-2xl font-black text-slate-900 block">{{ $count }}</span>
                        <span class="text-[10px] uppercase font-bold text-slate-500 block mt-0.5">Bags Available</span>
                    </div>
                    <div class="mt-3">
                        <span class="inline-block rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $badgeColor }}">
                            {{ $status }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Activity Stream & Operational Quick Actions -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Audit Activity Log Feed -->
        <div class="lg:col-span-2 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <h3 class="text-base font-bold text-slate-900">Audit Trail & Operations Stream</h3>
                <a href="{{ route('admin.activity-logs.index') }}" class="text-xs font-semibold text-red-600 hover:text-red-700">View Full Logs &rarr;</a>
            </div>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse($recentActivities as $activity)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $activity['message'] }}</p>
                            <p class="text-xs text-slate-500">By <span class="font-semibold">{{ $activity['user_name'] }}</span></p>
                        </div>
                        <span class="text-xs text-slate-400 font-mono">{{ $activity['created_at'] }}</span>
                    </div>
                @empty
                    <p class="py-4 text-sm text-slate-500 text-center">No recent activity logs recorded.</p>
                @endforelse
            </div>
        </div>

        <!-- System Quick Actions & Summary -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-4">Operational Summary</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-600 font-medium">Pending Requisitions</span>
                    <span class="font-bold text-slate-900 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs text-amber-800">{{ $pendingRequests }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-600 font-medium">This Month Donations</span>
                    <span class="font-bold text-slate-900">{{ $thisMonthStats['donations'] }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-600 font-medium">New Donors (This Month)</span>
                    <span class="font-bold text-slate-900">{{ $thisMonthStats['new_donors'] }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-600 font-medium">Fulfilled Requests</span>
                    <span class="font-bold text-slate-900">{{ $thisMonthStats['approved_requests'] }}</span>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 space-y-2">
                <a href="{{ route('admin.blood_requests.index') }}" class="block w-full text-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                    Review Blood Requests
                </a>
                <a href="{{ route('admin.reports.index') }}" class="block w-full text-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                    Export Compliance Reports
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
