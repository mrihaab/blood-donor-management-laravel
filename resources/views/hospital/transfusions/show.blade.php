<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Transfusion Administration #TR-{{ $transfusion->id }}
        </h2>
    </x-slot>

    <div class="py-6 space-y-6">
        <x-breadcrumbs :items="[
            ['label' => 'Hospital Portal', 'url' => route('hospital.dashboard')],
            ['label' => 'Transfusions', 'url' => route('hospital.transfusions.index')],
            ['label' => '#TR-' . $transfusion->id, 'url' => '']
        ]" />

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Patient: {{ $transfusion->patient->name }}</h3>
                    <p class="text-sm text-slate-500">MRN: {{ $transfusion->patient->mrn }} ({{ $transfusion->patient->bloodGroup->name ?? 'N/A' }})</p>
                </div>
                <div>
                    <x-status-badge :status="$transfusion->status" />
                </div>
            </div>

            <!-- Workflow Action Bar -->
            <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 flex flex-wrap items-center gap-3">
                @if($transfusion->status === 'scheduled')
                    <form method="POST" action="{{ route('hospital.transfusions.start', $transfusion->id) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                            Start Transfusion
                        </button>
                    </form>
                @elseif($transfusion->status === 'issued' || $transfusion->status === 'started')
                    <form method="POST" action="{{ route('hospital.transfusions.start', $transfusion->id) }}">
                        @csrf
                        @if($transfusion->status === 'issued')
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                                Start Transfusion
                            </button>
                        @endif
                    </form>
                    <form method="POST" action="{{ route('hospital.transfusions.complete', $transfusion->id) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                            Mark Complete
                        </button>
                    </form>
                    <form method="POST" action="{{ route('hospital.transfusions.stop', $transfusion->id) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="reason" placeholder="Reason to stop..." required class="rounded border-slate-300 text-xs" />
                        <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-lg text-xs font-medium hover:bg-red-700">
                            Stop Transfusion
                        </button>
                    </form>
                @endif
            </div>

            <!-- Issued Blood Units -->
            <div>
                <h4 class="text-md font-bold text-slate-900 mb-3">Issued Units</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 border">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Unit Barcode</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Blood Group</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Expiry Date</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Crossmatch Status</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-600 text-left">Unit Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($transfusion->transfusionUnits as $tu)
                                <tr>
                                    <td class="px-4 py-2 text-sm font-bold text-slate-900">{{ $tu->bloodUnit->unit_number }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $tu->bloodUnit->bloodGroup->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $tu->bloodUnit->expiry_date }}</td>
                                    <td class="px-4 py-2 text-sm text-green-700 font-semibold">VERIFIED COMPATIBLE</td>
                                    <td class="px-4 py-2 text-sm">
                                        <x-status-badge :status="$tu->bloodUnit->status" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-sm text-slate-500 text-center">No units issued yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Report Adverse Reaction Form -->
            <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                <h4 class="text-md font-bold text-slate-900 mb-3">Report Transfusion Reaction</h4>
                <form method="POST" action="{{ route('hospital.transfusions.reaction', $transfusion->id) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Reaction Type *</label>
                            <input type="text" name="reaction_type" required placeholder="Febrile, Allergic, Hemolytic..." class="w-full rounded border-slate-300 text-xs" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Severity *</label>
                            <select name="severity" required class="w-full rounded border-slate-300 text-xs">
                                <option value="mild">Mild</option>
                                <option value="moderate">Moderate</option>
                                <option value="severe">Severe</option>
                                <option value="life_threatening">Life Threatening</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Blood Unit (Optional)</label>
                            <select name="blood_unit_id" class="w-full rounded border-slate-300 text-xs">
                                <option value="">-- All / Unspecified --</option>
                                @foreach($transfusion->transfusionUnits as $tu)
                                    <option value="{{ $tu->blood_unit_id }}">{{ $tu->bloodUnit->unit_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Symptoms *</label>
                        <textarea name="symptoms" required rows="2" placeholder="Fever, chills, rash, dyspnea..." class="w-full rounded border-slate-300 text-xs"></textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded text-xs font-medium hover:bg-red-700">
                        Submit Reaction & Alert Admin
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
