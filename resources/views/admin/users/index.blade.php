@extends('layouts.admin')

@section('title', 'User Management')
@section('page_title', 'User & Staff Accounts')

@section('content')
<div class="space-y-6 pb-12" 
     x-data="{ 
         showCreateModal: false, 
         selectedRole: 'hospital', 
         isSubmitting: false,

         // Edit User Modal State
         showEditModal: false,
         editId: null,
         editName: '',
         editEmail: '',
         editRole: 'hospital',
         initialRole: 'hospital',
         editHospitalId: '',
         editStatus: 'active',
         editPassword: '',
         editPasswordConfirmation: '',
         isMasterAdmin: false,

         // 2-Step Role Confirmation Modal State
         showRoleConfirmModal: false,

         openEditModal(user) {
             this.editId = user.id;
             this.editName = user.name;
             this.editEmail = user.email;
             this.editRole = user.role;
             this.initialRole = user.role;
             this.editHospitalId = user.hospital_id || '';
             this.editStatus = user.status || 'active';
             this.editPassword = '';
             this.editPasswordConfirmation = '';
             this.isMasterAdmin = (user.email === '{{ env('ADMIN_EMAIL', 'admin@example.com') }}');
             this.isSubmitting = false;
             this.showRoleConfirmModal = false;
             this.showEditModal = true;
         },

         get editRoleLabel() {
             if (this.editRole === 'admin') return 'Administrator';
             if (this.editRole === 'hospital') return 'Hospital Staff';
             if (this.editRole === 'donor') return 'Donor';
             return this.editRole;
         },

         get initialRoleLabel() {
             if (this.initialRole === 'admin') return 'Administrator';
             if (this.initialRole === 'hospital') return 'Hospital Staff';
             if (this.initialRole === 'donor') return 'Donor';
             return this.initialRole;
         },

         handleEditSubmit(e) {
             if (!this.isMasterAdmin && this.editRole !== this.initialRole) {
                 e.preventDefault();
                 this.showRoleConfirmModal = true;
             } else {
                 this.isSubmitting = true;
             }
         },

         confirmRoleChangeAndSubmit() {
             this.isSubmitting = true;
             this.$refs.editUserForm.submit();
         }
     }">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">User Accounts Directory</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Manage administrator, hospital clinician, and donor user accounts.</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-rose-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-rose-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            <span>Add User / Hospital Staff</span>
        </button>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="p-4 text-xs md:text-sm text-emerald-900 dark:text-emerald-200 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between shadow-sm" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span><strong class="font-bold">Success!</strong> {{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 text-xs md:text-sm text-rose-900 dark:text-rose-200 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 flex items-center justify-between shadow-sm" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><strong class="font-bold">Security Alert:</strong> {{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 text-xs md:text-sm text-rose-900 dark:text-rose-200 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 shadow-sm" role="alert">
            <div class="font-bold mb-1">Please correct the following input errors:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- User Accounts Data Table -->
    <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5">User Name</th>
                        <th class="px-4 py-3.5">Email Address</th>
                        <th class="px-4 py-3.5">Role</th>
                        <th class="px-4 py-3.5">Associated Hospital</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Registered Date</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3.5 font-extrabold text-slate-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-600 dark:text-slate-400 text-xs">{{ $user->email }}</td>
                            <td class="px-4 py-3.5">
                                <x-status-badge :status="$user->role === 'admin' ? 'emergency' : ($user->role === 'hospital' ? 'approved' : 'available')" :label="$user->role === 'hospital' ? 'Hospital Staff' : ucfirst($user->role)" />
                            </td>
                            <td class="px-4 py-3.5 text-slate-900 dark:text-slate-100 font-semibold">
                                {{ $user->hospital->name ?? ($user->role === 'hospital' ? 'Unassigned' : 'N/A') }}
                            </td>
                            <td class="px-4 py-3.5">
                                <x-status-badge :status="$user->status === 'active' ? 'active' : 'expired'" :label="ucfirst($user->status ?? 'active')" />
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <button type="button" 
                                        @click="openEditModal({ id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', role: '{{ $user->role }}', hospital_id: {{ $user->hospital_id ?? 'null' }}, status: '{{ $user->status ?? 'active' }}' })"
                                        class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold rounded-xl transition focus:outline-none focus:ring-2 focus:ring-slate-400">
                                    Edit Account
                                </button>
                                @if($user->id !== auth()->id())
                                    <form id="form-delete-user-{{ $user->id }}" method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-delete-user-{{ $user->id }}', formId: 'form-delete-user-{{ $user->id }}' })" class="px-2.5 py-1.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 hover:bg-rose-100 text-xs font-bold rounded-xl transition focus:outline-none focus:ring-2 focus:ring-rose-500">
                                            Delete
                                        </button>
                                    </form>
                                    <x-confirm-dialog id="confirm-delete-user-{{ $user->id }}" title="Delete User Account" message="Are you sure you want to delete user account '{{ $user->name }}' ({{ $user->email }})? This action cannot be undone." confirmText="Delete Account" variant="danger" />
                                @else
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold italic">Current User</span>
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

        @if(method_exists($users, 'links') && $users->hasPages())
            <div class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Edit User Modal -->
    <div x-show="showEditModal" 
         x-cloak 
         @keydown.escape.window="showEditModal = false"
         @keydown.tab.prevent="
             const focusables = $el.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex=\'-1\'])');
             const first = focusables[0];
             const last = focusables[focusables.length - 1];
             if ($event.shiftKey && document.activeElement === first) { last.focus(); }
             else if (!$event.shiftKey && document.activeElement === last) { first.focus(); }
         "
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4" 
         role="dialog" 
         aria-modal="true">
        
        <div class="w-full max-w-lg bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-left space-y-4 max-h-[85vh] flex flex-col" @click.away="showEditModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 shrink-0">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit User Account (<span x-text="editName"></span>)</span>
                </h3>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form x-ref="editUserForm" 
                  method="POST" 
                  :action="'{{ url('admin/users') }}/' + editId" 
                  class="space-y-4 overflow-y-auto pr-1" 
                  @submit="handleEditSubmit($event)">
                @csrf
                @method('PUT')

                <div>
                    <label for="edit_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Full Name <span class="text-rose-600">*</span></label>
                    <input id="edit_name" type="text" name="name" x-model="editName" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div>
                    <label for="edit_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Email Address <span class="text-rose-600">*</span></label>
                    <input id="edit_email" type="email" name="email" x-model="editEmail" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div>
                    <label for="edit_role" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Account Role <span class="text-rose-600">*</span></label>
                    <select id="edit_role" name="role" x-model="editRole" :disabled="isMasterAdmin" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none disabled:opacity-60 disabled:bg-slate-100 dark:disabled:bg-slate-800">
                        <option value="hospital">Hospital Staff</option>
                        <option value="donor">Donor</option>
                        <option value="admin">Administrator</option>
                    </select>
                    <template x-if="isMasterAdmin">
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-medium">Master Administrator account role is protected and cannot be modified.</p>
                    </template>
                </div>

                <div x-show="editRole === 'hospital'">
                    <label for="edit_hospital_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Assign Hospital <span class="text-rose-600">*</span></label>
                    <select id="edit_hospital_id" name="hospital_id" x-model="editHospitalId" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <option value="">-- Select Hospital --</option>
                        @foreach($hospitals as $hosp)
                            <option value="{{ $hosp->id }}">{{ $hosp->name }} ({{ $hosp->city }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Account Status <span class="text-rose-600">*</span></label>
                    <select id="edit_status" name="status" x-model="editStatus" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 pt-3 space-y-3">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Reset Password (Optional)</p>
                    <div>
                        <label for="edit_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">New Password</label>
                        <input id="edit_password" type="password" name="password" x-model="editPassword" placeholder="Leave blank to keep existing password" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="edit_password_confirmation" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Confirm New Password</label>
                        <input id="edit_password_confirmation" type="password" name="password_confirmation" x-model="editPasswordConfirmation" placeholder="Repeat new password" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 shrink-0">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">Cancel</button>
                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 inline-flex items-center gap-1.5">
                        <template x-if="isSubmitting">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                        <span x-text="editRole !== initialRole && !isMasterAdmin ? 'Review Role Change & Save' : (isSubmitting ? 'Saving...' : 'Save Changes')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2-Step Safety Review Dialog for Role Change -->
    <div x-show="showRoleConfirmModal" 
         x-cloak 
         @keydown.escape.window="showRoleConfirmModal = false"
         @keydown.tab.prevent="
             const focusables = $el.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex=\'-1\'])');
             const first = focusables[0];
             const last = focusables[focusables.length - 1];
             if ($event.shiftKey && document.activeElement === first) { last.focus(); }
             else if (!$event.shiftKey && document.activeElement === last) { first.focus(); }
         "
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4" 
         role="dialog" 
         aria-modal="true">
        
        <div class="w-full max-w-lg bg-white dark:bg-[#0c1427] rounded-2xl border border-amber-300 dark:border-amber-700/60 shadow-2xl p-6 text-left space-y-4 max-h-[85vh] flex flex-col" @click.away="showRoleConfirmModal = false">
            <div class="flex items-center justify-between border-b border-amber-100 dark:border-amber-900/40 pb-3 shrink-0">
                <h3 class="text-base font-extrabold text-amber-900 dark:text-amber-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Security Review — User Role Modification</span>
                </h3>
                <button type="button" @click="showRoleConfirmModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4 overflow-y-auto pr-1">
                <div class="bg-slate-50 dark:bg-[#111c38] rounded-xl p-4 border border-slate-200 dark:border-slate-800 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">User Target:</span>
                        <span class="font-extrabold text-slate-900 dark:text-white" x-text="editName"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">Email Address:</span>
                        <span class="font-mono text-slate-700 dark:text-slate-300" x-text="editEmail"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">Current Role:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="initialRoleLabel"></span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 dark:border-slate-800 pt-2 mt-2">
                        <span class="text-amber-800 dark:text-amber-400 font-bold">Proposed New Role:</span>
                        <span class="font-black text-rose-600 dark:text-rose-400 text-sm" x-text="editRoleLabel"></span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/80 text-amber-900 dark:text-amber-200 text-xs space-y-1.5 leading-relaxed">
                    <div class="font-extrabold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Authorization Impact Statement</span>
                    </div>
                    <p>
                        Changing <strong x-text="editName"></strong>'s role from <strong x-text="initialRoleLabel"></strong> to <strong x-text="editRoleLabel"></strong> will update their system permissions according to existing backend authorization rules. Ensure this user is verified and authorized for these elevated privileges.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 shrink-0">
                <button type="button" @click="showRoleConfirmModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">Back to Edit</button>
                <button type="button" 
                        :disabled="isSubmitting"
                        @click="confirmRoleChangeAndSubmit()" 
                        class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-amber-500 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Updating Role...' : 'Confirm & Apply Role Change'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Create User / Staff Modal -->
    <div x-show="showCreateModal" 
         x-cloak 
         @keydown.escape.window="showCreateModal = false"
         @keydown.tab.prevent="
             const focusables = $el.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex=\'-1\'])');
             const first = focusables[0];
             const last = focusables[focusables.length - 1];
             if ($event.shiftKey && document.activeElement === first) { last.focus(); }
             else if (!$event.shiftKey && document.activeElement === last) { first.focus(); }
         "
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4" 
         role="dialog" 
         aria-modal="true">
        
        <div class="w-full max-w-lg bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-left space-y-4 max-h-[85vh] flex flex-col" @click.away="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 shrink-0">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>Add User Account / Staff</span>
                </h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4 overflow-y-auto pr-1" @submit="isSubmitting = true">
                @csrf
                <div>
                    <label for="create_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Full Name <span class="text-rose-600">*</span></label>
                    <input id="create_name" type="text" name="name" required placeholder="Dr. John Doe" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div>
                    <label for="create_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Email Address <span class="text-rose-600">*</span></label>
                    <input id="create_email" type="email" name="email" required placeholder="john@hospital.org" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div>
                    <label for="create_role" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Account Role <span class="text-rose-600">*</span></label>
                    <select id="create_role" name="role" x-model="selectedRole" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <option value="hospital">Hospital Staff</option>
                        <option value="donor">Donor</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <div x-show="selectedRole === 'hospital'">
                    <label for="create_hospital_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Assign Hospital <span class="text-rose-600">*</span></label>
                    <select id="create_hospital_id" name="hospital_id" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        @foreach($hospitals as $hosp)
                            <option value="{{ $hosp->id }}">{{ $hosp->name }} ({{ $hosp->city }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="create_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Account Status <span class="text-rose-600">*</span></label>
                    <select id="create_status" name="status" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>

                <div>
                    <label for="create_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Password <span class="text-rose-600">*</span></label>
                    <input id="create_password" type="password" name="password" required placeholder="Minimum 8 characters" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div>
                    <label for="create_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Confirm Password <span class="text-rose-600">*</span></label>
                    <input id="create_password_confirmation" type="password" name="password_confirmation" required placeholder="Repeat password" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 shrink-0">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">Cancel</button>
                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 inline-flex items-center gap-1.5">
                        <template x-if="isSubmitting">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                        <span x-text="isSubmitting ? 'Creating Account...' : 'Create Account'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
