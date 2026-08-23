<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Clinical Transfusions Operations') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <x-breadcrumbs :items="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Clinical Transfusions', 'url' => '']
        ]" />

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <form method="GET" action="{{ route('admin.transfusions.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                        <option value="">All Statuses</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                        <option value="started" {{ request('status') == 'started' ? 'selected' : '' }}>Started</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="stopped" {{ request('status') == 'stopped' ? 'selected' : '' }}>Stopped</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Search Patient</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Patient name or MRN..." class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-slate-800 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-slate-700">
                        Filter Operations
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">TR #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Patient / MRN</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Hospital</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Requisition</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Reactions</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($transfusions as $t)
                            <tr>
                                <td class="px-4 py-3 text-sm font-bold text-slate-900">#TR-{{ $t->id }}</td>
                                <td class="px-4 py-3 text-sm text-slate-900 font-medium">
                                    {{ $t->patient->name }} <br/>
                                    <span class="text-xs text-slate-500">MRN: {{ $t->patient->mrn }} ({{ $t->patient->bloodGroup->name ?? 'N/A' }})</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $t->hospital->name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700">#REQ-{{ $t->blood_request_id }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <x-status-badge :status="$t->status" />
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($t->reactions->count() > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            {{ $t->reactions->count() }} Reaction(s)
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">None</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('admin.transfusions.show', $t->id) }}" class="text-red-600 hover:text-red-900 font-medium">
                                        View Timeline &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center">
                                    <x-empty-state title="No Transfusions Found" description="No clinical transfusions match your query parameters." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transfusions->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
