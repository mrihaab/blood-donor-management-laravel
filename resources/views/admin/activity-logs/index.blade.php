@extends('layouts.admin')

@section('title', 'Activity Audit Log')
@section('page_title', 'System Activity Audit Log')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-3 md:space-y-0">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search log description..." class="rounded-md border-gray-300 shadow-sm text-sm focus:border-red-500 focus:ring-red-500">
            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Filter</button>
            @if(request('search'))
                <a href="{{ route('admin.activity-logs.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg">Clear</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 font-semibold uppercase text-xs">
                <tr>
                    <th class="py-3 px-4">Date & Time</th>
                    <th class="py-3 px-4">User (Causer)</th>
                    <th class="py-3 px-4">Action / Description</th>
                    <th class="py-3 px-4">Subject</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="py-3 px-4 font-medium text-gray-900">
                            {{ optional($log->causer)->name ?? 'System' }}
                            @if(optional($log->causer)->email)
                                <span class="text-xs text-gray-400">({{ $log->causer->email }})</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">{{ $log->description }}</td>
                        <td class="py-3 px-4">
                            @if($log->subject_type)
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                                    {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-400">No activity log entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
