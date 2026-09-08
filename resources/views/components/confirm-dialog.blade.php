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
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white focus:ring-rose-500',
        'warning' => 'bg-amber-600 hover:bg-amber-700 text-white focus:ring-amber-500',
        default => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
    };
@endphp

<div id="{{ $id }}" 
     class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/70 backdrop-blur-sm transition-opacity" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="{{ $id }}-title" 
     x-data="{ 
         targetFormId: null, 
         triggerEl: null, 
         isSubmitting: false,
         openModal(formId) {
             this.targetFormId = formId;
             this.triggerEl = document.activeElement;
             this.isSubmitting = false;
             document.getElementById('{{ $id }}').classList.remove('hidden');
             $nextTick(() => { $refs.confirmBtn?.focus(); });
         },
         closeModal() {
             document.getElementById('{{ $id }}').classList.add('hidden');
             if (this.triggerEl) { this.triggerEl.focus(); }
         },
         submitAction() {
             if (this.isSubmitting) return;
             this.isSubmitting = true;
             if (this.targetFormId) {
                 const form = document.getElementById(this.targetFormId);
                 if (form) form.submit();
             }
         }
     }" 
     @open-confirm.window="if ($event.detail.modalId === '{{ $id }}') { openModal($event.detail.formId); }" 
     @keydown.escape.window="closeModal()"
     @keydown.tab="
         const focusables = Array.from($el.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])'));
         if (focusables.length === 0) return;
         const first = focusables[0];
         const last = focusables[focusables.length - 1];
         if ($event.shiftKey && document.activeElement === first) {
             $event.preventDefault();
             last.focus();
         } else if (!$event.shiftKey && document.activeElement === last) {
             $event.preventDefault();
             first.focus();
         }
     ">
    
    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-[#0c1427] text-left align-middle shadow-2xl transition-all border border-slate-200 dark:border-slate-800 flex flex-col max-h-[85vh]">
            <!-- Fixed Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
                <h3 id="{{ $id }}-title" class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $title }}</span>
                </h3>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition focus:outline-none focus:ring-2 focus:ring-rose-500 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Scrollable Body -->
            <div class="p-6 overflow-y-auto space-y-3">
                <p class="text-xs md:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $message }}</p>
                {{ $slot ?? '' }}
            </div>

            <!-- Fixed Footer -->
            <div class="px-6 py-4 bg-slate-50 dark:bg-[#080d1a] border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-end gap-3 shrink-0">
                <button type="button" 
                        @click="closeModal()" 
                        class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition focus:outline-none focus:ring-2 focus:ring-slate-400">
                    {{ $cancelText }}
                </button>
                <button type="button" 
                        x-ref="confirmBtn"
                        :disabled="isSubmitting"
                        @click="submitAction()" 
                        class="rounded-xl px-5 py-2 text-xs font-extrabold shadow-md transition focus:outline-none focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-1.5 {{ $buttonClass }}">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Processing...' : '{{ $confirmText }}'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
