<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Clinical Transfusions') }}
            </h2>
            <a href="{{ route('hospital.transfusions.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">
                + Schedule Transfusion
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <x-breadcrumbs :items="[
            ['label' => 'Hospital Portal', 'url' => route('hospital.dashboard')],
            ['label' => 'Transfusions', 'url' => '']
        ]" />

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">TR #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Patient / MRN</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Requisition</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Units</th>
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
                                <td class="px-4 py-3 text-sm text-slate-700">#REQ-{{ $t->blood_request_id }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <x-status-badge :status="$t->status" />
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 font-medium">
                                    {{ $t->transfusionUnits->count() }} unit(s)
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('hospital.transfusions.show', $t->id) }}" class="text-red-600 hover:text-red-900 font-medium">
                                        Manage Transfusion &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center">
                                    <x-empty-state title="No Transfusions Recorded" description="Schedule a clinical transfusion for your hospital's approved blood requisitions." />
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
</x-app-layout>
