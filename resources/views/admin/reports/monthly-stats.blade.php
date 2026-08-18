@extends('layouts.admin')

@section('title', 'Monthly Statistics Report')
@section('page_title', 'Monthly Statistics')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Back to Reports</a>
        <a href="{{ route('admin.reports.monthly-stats', ['format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">Export PDF</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
        <h3 class="font-bold text-gray-900 text-lg">Monthly Donation Trends</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">Donations This Month</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $thisMonthStats['donations'] ?? 0 }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">New Donors Registered</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $thisMonthStats['new_donors'] ?? 0 }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">Blood Requests Filed</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $thisMonthStats['blood_requests'] ?? 0 }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500">Requests Approved</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $thisMonthStats['approved_requests'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
