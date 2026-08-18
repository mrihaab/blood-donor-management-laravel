@extends('layouts.admin')

@section('title', 'System Reports')
@section('page_title', 'System Reports & Export')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center font-bold text-xl mb-4">🧍</div>
            <h4 class="font-bold text-gray-900 text-lg">Donor Reports</h4>
            <p class="text-xs text-gray-500 mt-1">Export donor lists, blood group breakdowns, and contact information.</p>
        </div>
        <div class="mt-6 flex space-x-2">
            <a href="{{ route('admin.reports.donors') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-md flex-1 text-center">View Report</a>
            <a href="{{ route('admin.reports.donors', ['format' => 'pdf']) }}" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md text-center">PDF</a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center font-bold text-xl mb-4">💉</div>
            <h4 class="font-bold text-gray-900 text-lg">Donation Reports</h4>
            <p class="text-xs text-gray-500 mt-1">Review historical blood donation logs and donor participation rate.</p>
        </div>
        <div class="mt-6 flex space-x-2">
            <a href="{{ route('admin.reports.donations') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-md flex-1 text-center">View Report</a>
            <a href="{{ route('admin.reports.donations', ['format' => 'pdf']) }}" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md text-center">PDF</a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center font-bold text-xl mb-4">🗂️</div>
            <h4 class="font-bold text-gray-900 text-lg">Inventory Reports</h4>
            <p class="text-xs text-gray-500 mt-1">Export available units per blood type and low-stock alerts.</p>
        </div>
        <div class="mt-6 flex space-x-2">
            <a href="{{ route('admin.reports.inventory') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-md flex-1 text-center">View Report</a>
            <a href="{{ route('admin.reports.inventory', ['format' => 'pdf']) }}" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md text-center">PDF</a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center font-bold text-xl mb-4">📊</div>
            <h4 class="font-bold text-gray-900 text-lg">Monthly Statistics</h4>
            <p class="text-xs text-gray-500 mt-1">Monthly collection totals, request fulfillments, and operational growth.</p>
        </div>
        <div class="mt-6 flex space-x-2">
            <a href="{{ route('admin.reports.monthly-stats') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-md flex-1 text-center">View Report</a>
            <a href="{{ route('admin.reports.monthly-stats', ['format' => 'pdf']) }}" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md text-center">PDF</a>
        </div>
    </div>
</div>
@endsection
