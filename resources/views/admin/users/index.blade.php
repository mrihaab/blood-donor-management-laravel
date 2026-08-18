@extends('layouts.admin')

@section('title', 'User Management')
@section('page_title', 'User & Staff Accounts')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900">User Accounts Directory</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">User Name</th>
                    <th class="px-4 py-3">Email Address</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Registered Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-md {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $user->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500 italic">No user accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($users, 'links'))
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
