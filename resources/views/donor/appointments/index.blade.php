@extends('layouts.donor')

@section('title', 'My Appointments')

@section('content')
<div class="space-y-6" x-data="{ 
    cancelModalOpen: false, 
    activeAppId: null, 
    activeAppDate: '', 
    activeAppTime: '', 
    activeAppLocation: '' 
}">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Scheduled Donation Appointments</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Manage your upcoming blood collection visits and view appointment status history.</p>
        </div>
        <a href="{{ route('donor.appointments.create') }}" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs md:text-sm shadow-sm transition inline-flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ Schedule Appointment</span>
        </a>
    </div>

    <!-- Appointments Table Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm transition-colors space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">Appointment Records</h2>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total: {{ count($appointments) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-extrabold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="col" class="px-4 py-3">Scheduled Date</th>
                        <th scope="col" class="px-4 py-3">Preferred Time</th>
                        <th scope="col" class="px-4 py-3">Donation Location</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                        <th scope="col" class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($appointments as $app)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3.5 font-bold font-mono text-slate-900 dark:text-white">
                                {{ $app->appointment_date }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-xs text-slate-700 dark:text-slate-300">
                                {{ $app->appointment_time ?? '09:00 AM' }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-800 dark:text-slate-200 font-medium">
                                {{ $app->location ?? 'Main Blood Bank Center' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <x-status-badge :status="$app->status" />
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('donor.appointments.show', $app->id) }}" class="inline-flex items-center px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-bold transition">
                                    View
                                </a>

                                @if($app->status === 'scheduled')
                                    <a href="{{ route('donor.appointments.edit', $app->id) }}" class="inline-flex items-center px-2.5 py-1 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 rounded-lg text-xs font-bold transition">
                                        Reschedule
                                    </a>

                                    <button type="button" @click="
                                        activeAppId = {{ $app->id }};
                                        activeAppDate = '{{ $app->appointment_date }}';
                                        activeAppTime = '{{ $app->appointment_time ?? '09:00 AM' }}';
                                        activeAppLocation = '{{ addslashes($app->location ?? 'Main Blood Bank Center') }}';
                                        cancelModalOpen = true;
                                    " class="inline-flex items-center px-2.5 py-1 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-bold transition">
                                        Cancel
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400 italic">
                                <div class="flex flex-col items-center space-y-2">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>No scheduled appointments found.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cancellation Confirmation Dialog Modal -->
    <div x-show="cancelModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="cancel-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="cancelModalOpen" x-transition.opacity @click="cancelModalOpen = false" class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="cancelModalOpen" x-transition class="inline-block align-bottom bg-white dark:bg-[#0c1427] rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800 p-6 space-y-5">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 rounded-xl shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 id="cancel-modal-title" class="text-base font-extrabold text-slate-900 dark:text-white">Cancel Appointment</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Are you sure you want to cancel your scheduled donation visit?</p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-[#070d1a] p-4 rounded-xl border border-slate-200 dark:border-slate-800 text-xs space-y-1">
                    <p class="text-slate-700 dark:text-slate-300"><strong>Date:</strong> <span x-text="activeAppDate" class="font-mono"></span></p>
                    <p class="text-slate-700 dark:text-slate-300"><strong>Time:</strong> <span x-text="activeAppTime" class="font-mono"></span></p>
                    <p class="text-slate-700 dark:text-slate-300"><strong>Location:</strong> <span x-text="activeAppLocation"></span></p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" @click="cancelModalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition">
                        Keep Appointment
                    </button>
                    <form method="POST" :action="'/donor/appointments/' + activeAppId + '/cancel'" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                        @csrf
                        <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold rounded-xl shadow-sm transition">
                            <span x-text="isSubmitting ? 'Cancelling...' : 'Cancel Appointment'">Cancel Appointment</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
