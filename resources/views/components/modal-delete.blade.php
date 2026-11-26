<div x-data="{
        open: false,
        id: null,
        itemName: '',
        action: 'delete',
        title: 'Konfirmasi Hapus',
        note: 'Tindakan ini tidak dapat dibatalkan.',
        loading: false,
        async confirmDelete() {
            this.loading = true;
            await $wire[this.action](this.id);
            this.loading = false;
            this.open = false;
        }
     }"
     @open-delete-modal.window="
        id = $event.detail.id;
        itemName = $event.detail.name ?? 'data ini';
        action = $event.detail.action ?? 'delete';
        title = $event.detail.title ?? 'Konfirmasi Hapus';
        note = $event.detail.note ?? 'Data yang sudah dihapus tidak dapat dikembalikan.';
        open = true;
     "
     @keydown.escape.window="if (open && !loading) open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="if (!loading) open = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm">
    </div>

    <!-- Box Modal Delete -->
    <div x-show="open"
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="relative w-full max-w-md bg-slate-800 border border-rose-500/20 shadow-[0_25px_60px_-15px_rgba(225,29,72,0.25)] rounded-3xl p-6 text-center z-10">

        <!-- Ikon Peringatan Glow Merah -->
        <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center mb-4 shadow-[0_0_25px_rgba(225,29,72,0.25)]">
            <x-heroicon-o-exclamation-triangle class="w-7 h-7" />
        </div>

        <h3 class="text-xl font-bold text-white tracking-tight" x-text="title"></h3>

        <p class="text-sm text-slate-400 mt-2 leading-relaxed">
            Apakah kamu yakin ingin menghapus
            <span class="font-semibold text-white" x-text="itemName"></span>?
        </p>
        <p class="text-xs text-rose-400/80 mt-1" x-text="note"></p>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-center gap-3 mt-6 pt-5 border-t border-white/10">
            <button type="button" @click="open = false" :disabled="loading"
                class="flex-1 px-5 py-2.5 rounded-xl bg-slate-700 text-white hover:bg-slate-600 font-medium hover:bg-white/10 hover:text-white transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" @click="confirmDelete()" :disabled="loading"
                class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 text-white font-semibold shadow-lg shadow-rose-500/25 hover:shadow-rose-500/50 hover:-translate-y-0.5 active:scale-95 transition-all duration-150 cursor-pointer disabled:opacity-60">
                <span x-show="loading" class="loading loading-spinner loading-xs"></span>
                <span x-text="loading ? 'Menghapus...' : 'Ya, Hapus'"></span>
            </button>
        </div>
    </div>
</div>