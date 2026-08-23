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
