@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-4">Request Blood</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('donor.blood_requests.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="blood_group" class="block font-medium">Blood Group</label>
            <input type="text" name="blood_group" id="blood_group" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label for="city" class="block font-medium">City</label>
            <input type="text" name="city" id="city" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label for="needed_date" class="block font-medium">Needed Date</label>
            <input type="date" name="needed_date" id="needed_date" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label for="reason" class="block font-medium">Reason</label>
            <textarea name="reason" id="reason" rows="3" class="w-full border rounded px-3 py-2"></textarea>
        </div>

        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            Submit Request
        </button>
    </form>
</div>
@endsection
