@props([
    'id',
    'title',
    'message',
    'confirmText' => 'Confirm Action',
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

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" x-data="{ targetFormId: null }" @open-confirm.window="if ($event.detail.modalId === '{{ $id }}') { targetFormId = $event.detail.formId; document.getElementById('{{ $id }}').classList.remove('hidden'); }" @keydown.escape.window="document.getElementById('{{ $id }}').classList.add('hidden')">
    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-2xl transition-all border border-slate-200">
            <h3 id="{{ $id }}-title" class="text-lg font-bold text-slate-900">{{ $title }}</h3>
            <div class="mt-2">
                <p class="text-sm text-slate-500">{{ $message }}</p>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="document.getElementById('{{ $id }}').classList.add('hidden')" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition focus:outline-none focus:ring-2 focus:ring-slate-400">
                    {{ $cancelText }}
                </button>
                <button type="button" @click="if (targetFormId) { document.getElementById(targetFormId).submit(); } document.getElementById('{{ $id }}').classList.add('hidden');" class="rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2 focus:ring-red-500 {{ $buttonClass }}">
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>
