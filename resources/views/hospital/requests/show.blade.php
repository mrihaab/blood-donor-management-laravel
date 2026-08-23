@extends('layouts.hospital')

@section('title', 'Requisition Details')
@section('page_title', 'Requisition #REQ-' . $bloodRequest->id)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Blood Requisition #REQ-{{ $bloodRequest->id }}</h2>
            <p class="text-xs text-gray-500 mt-1">Submitted on {{ $bloodRequest->created_at->format('M d, Y \a\t H:i') }}</p>
        </div>
        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $bloodRequest->status === 'approved' ? 'bg-blue-100 text-blue-800' : ($bloodRequest->status === 'dispensed' ? 'bg-green-100 text-green-800' : ($bloodRequest->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
            {{ ucfirst($bloodRequest->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase text-gray-500 tracking-wider">Requisition Clinical Specification</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-xs text-gray-500 block">Patient Name</span>
                    <span class="font-semibold text-gray-900">{{ $bloodRequest->patient->name ?? $bloodRequest->patient_name }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Hospital</span>
                    <span class="font-semibold text-gray-900">{{ $bloodRequest->hospital->name ?? $bloodRequest->hospital }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Blood Group Requested</span>
                    <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md inline-block mt-0.5">
                        {{ $bloodRequest->blood_group }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Quantity</span>
                    <span class="font-semibold text-gray-900">{{ $bloodRequest->units_needed }} bag(s)</span>
                </div>
            </div>

            @if($bloodRequest->reason)
                <div class="pt-3 border-t border-gray-100">
                    <span class="text-xs text-gray-500 block">Clinical Indication</span>
                    <p class="text-xs text-gray-800 mt-1 italic">{{ $bloodRequest->reason }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase text-gray-500 tracking-wider">Fulfillment & Audit Tracking</h3>
            
            @if($bloodRequest->status === 'approved' || $bloodRequest->status === 'dispensed')
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-900">
                    <p class="font-bold mb-1">Stock Allocated via FEFO</p>
                    <p>Physical unit bags have been reserved/allocated from central inventory by administrative operations.</p>
                </div>
            @elseif($bloodRequest->status === 'rejected')
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-xs text-red-900">
                    <p class="font-bold mb-1">Requisition Declined</p>
                    <p>This requisition was rejected by blood bank administration.</p>
                </div>
            @else
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-xs text-yellow-900">
                    <p class="font-bold mb-1">Pending Administrative Review</p>
                    <p>Awaiting central blood bank admin approval and FEFO inventory allocation.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
