@extends('layouts.admin')

@section('title', 'Send Notification')
@section('page_title', 'Send Notification Alert')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.notifications.store') }}" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">Notification Title</label>
            <input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Target Group</label>
            <select name="target" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                <option value="all">All Registered Donors</option>
                <option value="active">Active Donors Only</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Message Content</label>
            <textarea name="message" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg text-sm hover:bg-red-700">Send Notification</button>
        </div>
    </form>
</div>
@endsection
