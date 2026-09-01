@extends('layouts.admin')

@section('title', 'Monthly Statistics Report')
@section('page_title', 'Monthly Statistics')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Back to Reports</a>
        <a href="{{ route('admin.reports.monthly-stats', ['format' => 'pdf', 'month' => $month, 'year' => $year]) }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">Export Executive Summary PDF</a>
    </div>

    <!-- Monthly Executive Summary Header -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-xl">Monthly Operational & Clinical Executive Summary</h3>
                <p class="text-xs text-slate-500 mt-0.5">Overview for {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</p>
            </div>

            <!-- Month / Year Selector Filter -->
            <form method="GET" action="{{ route('admin.reports.monthly-stats') }}" class="flex items-center gap-2">
                <select name="month" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold focus:border-red-500 focus:outline-none">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
                <select name="year" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold focus:border-red-500 focus:outline-none">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition">Filter</button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="p-4 bg-red-50 rounded-xl border border-red-100">
                <p class="text-xs font-bold uppercase text-red-700">Donation Units Collected</p>
                <p class="text-3xl font-extrabold text-red-900 mt-1">{{ $stats['donations'] }}</p>
            </div>
            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                <p class="text-xs font-bold uppercase text-blue-700">New Donors Registered</p>
                <p class="text-3xl font-extrabold text-blue-900 mt-1">{{ $stats['new_donors'] }}</p>
            </div>
            <div class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                <p class="text-xs font-bold uppercase text-purple-700">Requisitions Filed</p>
                <p class="text-3xl font-extrabold text-purple-900 mt-1">{{ $stats['blood_requests'] }}</p>
            </div>
            <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                <p class="text-xs font-bold uppercase text-amber-700">Requisitions Approved</p>
                <p class="text-3xl font-extrabold text-amber-900 mt-1">{{ $stats['approved_requests'] }}</p>
            </div>
            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                <p class="text-xs font-bold uppercase text-emerald-700">Dispensed to Hospitals</p>
                <p class="text-3xl font-extrabold text-emerald-900 mt-1">{{ $stats['dispensed_units'] }}</p>
            </div>
        </div>

        @if(count($bloodGroupStats) > 0)
            <div class="pt-4 border-t border-slate-100 space-y-3">
                <h4 class="text-sm font-bold text-slate-900">Blood Group Intake Distribution for {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($bloodGroupStats as $bgStat)
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 flex items-center justify-between">
                            <span class="font-bold text-xs rounded bg-red-700 px-2 py-0.5 text-white">{{ $bgStat->name }}</span>
                            <span class="font-extrabold text-slate-900 text-sm">{{ $bgStat->total }} Unit(s)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
