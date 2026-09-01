@extends('layouts.admin')

@section('title', 'User Management')
@section('page_title', 'User & Staff Accounts')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">User Accounts Directory</h1>
            <p class="text-sm text-slate-500">Manage administrator, hospital clinician, and donor user accounts.</p>
        </div>
        <button onclick="document.getElementById('create-user-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 shadow-sm transition">
            <span>👤</span>
            <span>+ Add New User / Hospital Staff</span>
        </button>
    </div>

    @if (session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            <span class="font-bold">Success!</span> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            <span class="font-bold">Error!</span> {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">User Name</th>
                        <th class="px-4 py-3">Email Address</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Associated Hospital</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Registered Date</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 font-mono text-slate-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-md {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : ($user->role === 'hospital' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $user->role === 'hospital' ? 'Hospital Staff' : ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-700 font-semibold">
                                {{ $user->hospital->name ?? ($user->role === 'hospital' ? 'Unassigned' : 'N/A') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($user->status ?? 'active') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete user account {{ $user->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-xs font-semibold">Delete Account</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400 font-semibold italic">Current User</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500 italic">No user accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($users, 'links'))
            <div class="mt-4 border-t border-slate-200 pt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create User / Staff Modal -->
<div id="create-user-modal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900">Add User Account / Hospital Staff</h3>
            <button onclick="document.getElementById('create-user-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Full Name *</label>
                <input type="text" name="name" required placeholder="Dr. John Doe" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Email Address *</label>
                <input type="email" name="email" required placeholder="john@hospital.org" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Account Role *</label>
                <select name="role" id="modal-role-select" onchange="toggleHospitalDropdown(this.value)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                    <option value="hospital">Hospital Staff</option>
                    <option value="donor">Donor</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div id="hospital-select-container">
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Assign Hospital *</label>
                <select name="hospital_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                    @foreach($hospitals as $hosp)
                        <option value="{{ $hosp->id }}">{{ $hosp->name }} ({{ $hosp->city }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Account Status *</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="blocked">Blocked</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Password *</label>
                <input type="password" name="password" required placeholder="Minimum 8 characters" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Confirm Password *</label>
                <input type="password" name="password_confirmation" required placeholder="Repeat password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('create-user-modal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow">Create User</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleHospitalDropdown(role) {
        const container = document.getElementById('hospital-select-container');
        if (role === 'hospital') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
</script>
@endsection
