@props([
    'id',
    'title',
    'message',
    'actionUrl',
    'method' => 'POST',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'variant' => 'danger', // danger, warning, primary
])

@php
    $buttonClass = match($variant) {
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white',
        'warning' => 'bg-amber-600 hover:bg-amber-700 text-white',
        default => 'bg-red-600 hover:bg-red-700 text-white',
    };
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 backdrop-blur-sm transition-opacity">
    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
            <h3 class="text-lg font-bold leading-6 text-slate-900">{{ $title }}</h3>
            <div class="mt-2">
                <p class="text-sm text-slate-500">{{ $message }}</p>
            </div>

            <form action="{{ $actionUrl }}" method="POST" class="mt-6 flex justify-end gap-3">
                @csrf
                @if(strtoupper($method) !== 'POST')
                    @method($method)
                @endif
                <button type="button" onclick="document.getElementById('{{ $id }}').classList.add('hidden')" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                    {{ $cancelText }}
                </button>
                <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition {{ $buttonClass }}">
                    {{ $confirmText }}
                </button>
            </form>
        </div>
    </div>
</div>
