@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">
    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Donors</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalDonors }}</p>
                <p class="text-xs text-green-600 font-medium mt-1">{{ $activeDonors }} Active Donors</p>
            </div>
            <div class="p-3 bg-red-50 text-red-600 rounded-lg text-2xl">🧍</div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Blood Requests</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalRequests }}</p>
                <p class="text-xs text-amber-600 font-medium mt-1">{{ $pendingRequests }} Pending Approval</p>
            </div>
            <div class="p-3 bg-amber-50 text-amber-600 rounded-lg text-2xl">📥</div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Donations</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalDonations }}</p>
                <p class="text-xs text-blue-600 font-medium mt-1">{{ $thisMonthStats['donations'] ?? 0 }} This Month</p>
            </div>
            <div class="p-3 bg-blue-50 text-blue-600 rounded-lg text-2xl">💉</div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Low Stock Warnings</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ count($lowStockAlerts) }}</p>
                <p class="text-xs text-gray-500 mt-1">Threshold: &lt; {{ $lowStockThreshold }} units</p>
            </div>
            <div class="p-3 bg-red-100 text-red-700 rounded-lg text-2xl">⚠️</div>
        </div>
    </div>

    <!-- Inventory Overview & Stock Warning Alert -->
    @if(count($lowStockAlerts) > 0)
    <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-r-xl">
        <div class="flex items-center">
            <span class="text-xl mr-3">🚨</span>
            <div>
                <h4 class="font-bold text-red-800">Critical Low Stock Alert</h4>
                <p class="text-sm text-red-700">The following blood groups have dipped below the minimum threshold ({{ $lowStockThreshold }} units):</p>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($lowStockAlerts as $alert)
                        <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                            {{ $alert['blood_group'] }}: {{ $alert['units_available'] }} units available
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Blood Stock Levels -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 text-lg">Blood Inventory Summary</h3>
                <a href="{{ route('admin.inventory.index') }}" class="text-sm text-red-600 hover:underline font-semibold">Manage Inventory &rarr;</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($bloodInventory as $item)
                    <div class="p-4 rounded-lg border {{ $item['units_available'] < $lowStockThreshold ? 'border-red-200 bg-red-50/50' : 'border-gray-100 bg-gray-50' }}">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 bg-red-600 text-white font-extrabold text-xs rounded-md">{{ $item['blood_group'] }}</span>
                            <span class="text-xs text-gray-500">{{ $item['last_updated'] }}</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 mt-3">{{ $item['units_available'] }} <span class="text-xs font-normal text-gray-500">units</span></p>
                        <p class="text-xs text-gray-500 mt-1">{{ $item['units_requested'] }} requested</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Activity Log -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-900 text-lg mb-4">Recent Activity</h3>
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @forelse($recentActivities as $activity)
                    <div class="flex items-start space-x-3 text-sm pb-3 border-b border-gray-50">
                        <span class="p-1.5 bg-gray-100 rounded text-gray-600 mt-0.5">📌</span>
                        <div>
                            <p class="text-gray-800">{{ $activity['message'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">By {{ $activity['user_name'] }} • {{ $activity['created_at'] }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 italic">No recent system activity recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
