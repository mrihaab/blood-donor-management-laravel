@extends('layouts.admin')

@section('title', 'Manage Donors')
@section('page_title', 'Registered Donors')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Donor Directory</h3>
            <p class="text-xs text-gray-500">View, manage, and audit registered blood donor profiles.</p>
        </div>
        <a href="{{ route('admin.donors.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition shadow-sm">
            + Register New Donor
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            <span class="font-bold">Success!</span> {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Name & Email</th>
                    <th class="px-4 py-3">Blood Group</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($donors as $donor)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-semibold text-gray-900">
                            {{ $donor->user->name ?? 'N/A' }}
                            <span class="block text-xs font-mono font-normal text-gray-500">{{ $donor->user->email ?? 'No Email' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                {{ $donor->bloodGroup->name ?? 'Unspecified' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $donor->contact_number ?? 'Not Provided' }}</td>
                        <td class="px-4 py-3">{{ $donor->city ?? 'Not Provided' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ ($donor->status ?? 'active') === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($donor->status ?? 'active') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.donors.show', $donor->id) }}" class="text-blue-600 hover:underline text-xs font-semibold">View</a>
                            <a href="{{ route('admin.donors.edit', $donor->id) }}" class="text-amber-600 hover:underline text-xs font-semibold">Edit</a>
                            <form method="POST" action="{{ route('admin.donors.destroy', $donor->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete donor {{ $donor->user->name ?? '' }}? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs font-semibold">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">No donors registered in system.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($donors, 'links'))
        <div class="mt-4 border-t border-gray-100 pt-4">
            {{ $donors->links() }}
        </div>
    @endif
</div>
@endsection
