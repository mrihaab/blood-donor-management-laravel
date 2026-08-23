<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Schedule Clinical Transfusion') }}
        </h2>
    </x-slot>

    <div class="py-6 space-y-6 max-w-4xl mx-auto">
        <x-breadcrumbs :items="[
            ['label' => 'Hospital Portal', 'url' => route('hospital.dashboard')],
            ['label' => 'Transfusions', 'url' => route('hospital.transfusions.index')],
            ['label' => 'Schedule', 'url' => '']
        ]" />

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <form method="POST" action="{{ route('hospital.transfusions.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Approved Requisition *</label>
                    <select name="blood_request_id" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                        <option value="">-- Select Approved Requisition --</option>
                        @foreach($approvedRequests as $req)
                            <option value="{{ $req->id }}">
                                #REQ-{{ $req->id }} - Patient: {{ $req->patient_name }} ({{ $req->blood_group }}, {{ $req->units_needed }} units)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Target Patient *</label>
                    <select name="patient_id" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                        <option value="">-- Select Patient --</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}">
                                {{ $patient->name }} (MRN: {{ $patient->mrn }}, Blood Group: {{ $patient->bloodGroup->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Clinical Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm" placeholder="Pre-transfusion vitals, ward location, special instructions..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('hospital.transfusions.index') }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-300">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">
                        Schedule Transfusion
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
