@extends('layouts.hospital')

@section('title', 'Inpatient Directory')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('hospital.dashboard')],
        ['label' => 'Inpatient Directory']
    ]" />

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Inpatient Registry</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Hospital patient medical record directory and blood group tracking.</p>
        </div>

        <a href="{{ route('hospital.patients.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-lg shadow-blue-600/30 flex items-center gap-2 self-start sm:self-auto">
            <span>+ Register New Inpatient</span>
        </a>
    </div>

    <!-- Patient Directory Table Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm transition-colors space-y-4">
        
        <!-- Search Filter Form -->
        <form method="GET" action="{{ route('hospital.patients.index') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="flex flex-col sm:flex-row gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by patient name, MRN, or phone..." class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none placeholder-slate-400 dark:placeholder-slate-500 transition">
            </div>
            <button type="submit" :disabled="isSubmitting" class="px-5 py-2.5 bg-slate-900 dark:bg-slate-800 text-white font-bold rounded-xl text-xs hover:bg-slate-800 dark:hover:bg-slate-700 transition disabled:opacity-50 inline-flex items-center justify-center gap-1.5">
                <template x-if="isSubmitting">
                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </template>
                <span x-text="isSubmitting ? 'Searching...' : 'Search Patients'">Search Patients</span>
            </button>
            @if(request('search'))
                <a href="{{ route('hospital.patients.index') }}" class="px-4 py-2.5 bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 font-bold rounded-xl text-xs border border-rose-200 dark:border-rose-800 transition flex items-center justify-center">
                    Clear
                </a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-extrabold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 rounded-l-xl">MRN</th>
                        <th scope="col" class="px-4 py-3.5">Patient Name</th>
                        <th scope="col" class="px-4 py-3.5">Gender</th>
                        <th scope="col" class="px-4 py-3.5">Date of Birth</th>
                        <th scope="col" class="px-4 py-3.5">Blood Group</th>
                        <th scope="col" class="px-4 py-3.5 text-right rounded-r-xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($patients as $patient)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-4 font-mono font-black text-blue-600 dark:text-blue-400">{{ $patient->mrn }}</td>
                            <td class="px-4 py-4 font-bold text-slate-900 dark:text-white">{{ $patient->name }}</td>
                            <td class="px-4 py-4 capitalize text-slate-600 dark:text-slate-300">{{ $patient->gender }}</td>
                            <td class="px-4 py-4 text-xs font-mono text-slate-500 dark:text-slate-400">
                                {{ is_object($patient->date_of_birth) ? $patient->date_of_birth->format('M d, Y') : ($patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('M d, Y') : 'N/A') }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-rose-50 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80 shadow-xs">
                                    🩸 {{ $patient->bloodGroup->name ?? 'Unmapped' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right space-x-2">
                                <a href="{{ route('hospital.patients.show', $patient->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 dark:bg-blue-600/20 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white font-bold rounded-lg text-xs border border-blue-200 dark:border-blue-500/30 transition">
                                    View
                                </a>
                                <a href="{{ route('hospital.patients.edit', $patient->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold rounded-lg text-xs border border-slate-300 dark:border-slate-700 transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <x-empty-state 
                                    title="No Inpatients Registered" 
                                    description="No patient medical records match your search query."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($patients->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
