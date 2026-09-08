@extends('layouts.admin')

@section('title', 'Appointments')
@section('page_title', 'Donation Appointments Directory')

@section('content')
<div class="space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Page Header & Action Row -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Donation Appointments Schedule</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Clinical appointment scheduling and blood donation intake processing.</p>
        </div>
        <a href="{{ route('admin.appointments.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-rose-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-rose-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Schedule New Appointment</span>
        </a>
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
                <span><strong class="font-bold">Error:</strong> {{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Appointments Data Table -->
    <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5">Donor Name</th>
                        <th class="px-4 py-3.5">ABO/Rh Profile</th>
                        <th class="px-4 py-3.5">Appointment Date</th>
                        <th class="px-4 py-3.5">Time Slot</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                    @forelse($appointments as $app)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3.5 font-extrabold text-slate-900 dark:text-white">
                                <a href="{{ route('admin.appointments.show', $app->id) }}" class="hover:text-rose-600 dark:hover:text-rose-400 transition">
                                    {{ $app->donor->user->name ?? 'Donor #'.$app->donor_id }}
                                </a>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-mono font-extrabold bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    {{ $app->donor->bloodGroup->name ?? 'Unspecified' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-xs text-slate-800 dark:text-slate-200">{{ $app->appointment_date }}</td>
                            <td class="px-4 py-3.5 font-mono text-xs text-slate-600 dark:text-slate-400">{{ $app->appointment_time ?? '09:00 AM' }}</td>
                            <td class="px-4 py-3.5">
                                <x-status-badge :status="$app->status" />
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                @if($app->status === 'scheduled')
                                    <a href="{{ route('admin.appointments.show', $app->id) }}" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold rounded-xl transition shadow-sm inline-flex items-center gap-1">
                                        <span>Start Donation Intake</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.appointments.mark_completed', $app->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 text-xs font-bold rounded-xl transition">Complete</button>
                                    </form>
                                    <form id="form-cancel-app-{{ $app->id }}" method="POST" action="{{ route('admin.appointments.mark_cancelled', $app->id) }}" class="inline">
                                        @csrf
                                        <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-cancel-app-{{ $app->id }}', formId: 'form-cancel-app-{{ $app->id }}' })" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 text-xs font-bold rounded-xl transition">
                                            Cancel
                                        </button>
                                    </form>
                                    <x-confirm-dialog id="confirm-cancel-app-{{ $app->id }}" title="Cancel Appointment" message="Are you sure you want to cancel appointment #{{ $app->id }} for {{ $app->donor->user->name ?? 'Donor' }}?" confirmText="Cancel Appointment" variant="warning" />
                                @else
                                    <a href="{{ route('admin.appointments.show', $app->id) }}" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 text-xs font-bold rounded-xl transition">
                                        View Details
                                    </a>
                                @endif

                                <form id="form-delete-app-{{ $app->id }}" method="POST" action="{{ route('admin.appointments.destroy', $app->id) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-delete-app-{{ $app->id }}', formId: 'form-delete-app-{{ $app->id }}' })" class="px-2.5 py-1.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 hover:bg-rose-100 text-xs font-bold rounded-xl transition">
                                        Delete
                                    </button>
                                </form>
                                <x-confirm-dialog id="confirm-delete-app-{{ $app->id }}" title="Delete Appointment Record" message="Are you sure you want to delete appointment record #{{ $app->id }}? This action cannot be undone." confirmText="Delete Record" variant="danger" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500 italic">No scheduled appointments found in directory.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
