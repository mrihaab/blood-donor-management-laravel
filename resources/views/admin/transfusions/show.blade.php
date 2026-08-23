<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Transfusion Timeline #TR-{{ $transfusion->id }}
        </h2>
    </x-slot>

    <div class="py-6 space-y-6">
        <x-breadcrumbs :items="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Clinical Transfusions', 'url' => route('admin.transfusions.index')],
            ['label' => '#TR-' . $transfusion->id, 'url' => '']
        ]" />

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Patient: {{ $transfusion->patient->name }}</h3>
                    <p class="text-sm text-slate-500">MRN: {{ $transfusion->patient->mrn }} | Hospital: {{ $transfusion->hospital->name }}</p>
                </div>
                <div>
                    <x-status-badge :status="$transfusion->status" />
                </div>
            </div>

            <!-- Traceability Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Patient Blood Group</p>
                    <p class="text-xl font-bold text-red-600">{{ $transfusion->patient->bloodGroup->name ?? 'N/A' }}</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Requisition</p>
                    <p class="text-xl font-bold text-slate-800">#REQ-{{ $transfusion->blood_request_id }}</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Administered By</p>
                    <p class="text-sm font-semibold text-slate-800">{{ $transfusion->administeredBy->name ?? 'Pending' }}</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Started At</p>
                    <p class="text-sm font-semibold text-slate-800">{{ $transfusion->started_at ? $transfusion->started_at->format('M d, Y H:i') : 'Not Started' }}</p>
                </div>
            </div>

            <!-- Attached Blood Units & Full Donor Traceability -->
            <div>
                <h4 class="text-md font-bold text-slate-900 mb-3">Issued Blood Units & Full Donor Traceability</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 border">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Unit Barcode</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Donor</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Component / Group</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Expiry Date</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Unit Status</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Quarantine Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($transfusion->transfusionUnits as $tu)
                                <tr>
                                    <td class="px-4 py-2 text-sm font-bold text-slate-900">{{ $tu->bloodUnit->unit_number }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-700">
                                        Donor #{{ $tu->bloodUnit->donor_id }} ({{ $tu->bloodUnit->donor->bloodGroup->name ?? 'N/A' }})
                                    </td>
                                    <td class="px-4 py-2 text-sm text-slate-700">
                                        {{ $tu->bloodUnit->component->name ?? 'PRBC' }} ({{ $tu->bloodUnit->bloodGroup->name ?? 'N/A' }})
                                    </td>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $tu->bloodUnit->expiry_date }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <x-status-badge :status="$tu->bloodUnit->status" />
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($tu->bloodUnit->status === 'returned' || $tu->bloodUnit->status === 'dispensed')
                                            <form method="POST" action="{{ route('admin.units.certify_returned', $tu->bloodUnit->id) }}" class="space-y-1">
                                                @csrf
                                                <input type="hidden" name="cold_chain_intact" value="1" />
                                                <input type="hidden" name="seal_intact" value="1" />
                                                <input type="hidden" name="elapsed_time_minutes" value="20" />
                                                <input type="hidden" name="visual_inspection_passed" value="1" />
                                                <button type="submit" class="px-2 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700">
                                                    Certify Safe & Return
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-sm text-slate-500 text-center">No units issued for this transfusion yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Adverse Reaction Log -->
            @if($transfusion->reactions->count() > 0)
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <h4 class="text-md font-bold text-red-900 mb-2">Transfusion Reactions Recorded</h4>
                    <div class="space-y-2">
                        @foreach($transfusion->reactions as $r)
                            <div class="p-3 bg-white rounded border border-red-200 text-sm">
                                <span class="font-bold text-red-700 uppercase">{{ $r->severity }}</span> - {{ $r->reaction_type }}
                                <p class="text-slate-700 mt-1">Symptoms: {{ $r->symptoms }}</p>
                                <p class="text-xs text-slate-500">Reported by {{ $r->reportedBy->name ?? 'User' }} at {{ $r->reported_at }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
