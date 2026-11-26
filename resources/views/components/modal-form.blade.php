@props([
    'show',
    'title' => 'Form Data',
    'submit' => 'save',
    'submitText' => 'Simpan',
    'maxWidth' => 'max-w-lg',
])

<div x-data="{ open: $wire.entangle('{{ $show }}').live }"
     x-show="open"
     x-cloak
     @keydown.escape.window="if (open) open = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">

    <!-- Backdrop persis versi asli -->
    <div x-show="open"
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm">
    </div>

    <!-- Box Modal persis versi asli (bg-slate-800, tanpa garis header & tombol X) -->
    <div x-show="open"
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="relative w-full {{ $maxWidth }} max-h-[90vh] overflow-y-auto my-auto bg-slate-800 border border-white/10 shadow-2xl rounded-3xl p-5 sm:p-6 z-10"

        <h3 class="font-bold text-xl text-white mb-6">{{ $title }}</h3>

        <form wire:submit.prevent="{{ $submit }}" class="space-y-4">
            {{ $slot }}

            <!-- Footer Tombol persis versi asli -->
            <div class="flex items-center justify-end gap-2 mt-8 border-t border-white/10 pt-6">
                <button type="button" @click="open = false"
                    class="px-6 py-2.5 rounded-xl bg-slate-700 text-white font-medium hover:bg-slate-600 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-semibold shadow-lg hover:shadow-teal-500/50 hover:-translate-y-0.5 transition-all cursor-pointer disabled:opacity-60">
                    <span wire:loading wire:target="{{ $submit }}" class="loading loading-spinner loading-xs"></span>
                    <span>{{ $submitText }}</span>
                </button>
            </div>
        </form>
    </div>
</div>