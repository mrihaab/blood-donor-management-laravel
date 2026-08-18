@extends('layouts.admin')

@section('title', 'Notification Details')
@section('page_title', 'Notification Details')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
    <div class="border-b pb-4">
        <h3 class="text-xl font-bold text-gray-900">{{ $notification->title ?? $notification->subject ?? 'Notification Details' }}</h3>
        <p class="text-xs text-gray-400 mt-1">Sent on {{ $notification->created_at->format('F d, Y \a\t h:i A') }}</p>
    </div>

    <div class="text-gray-700 text-sm leading-relaxed">
        {{ $notification->message ?? $notification->content ?? 'No content provided.' }}
    </div>

    <div class="pt-4 flex justify-end">
        <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Back</a>
    </div>
</div>
@endsection
