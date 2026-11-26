<!-- MODAL TAMBAH / EDIT GRUP MENU -->
<x-modal-form show="isGroupModalOpen"
    :title="$groupId ? 'Edit Grup Menu' : 'Tambah Grup Menu'"
    submit="saveGroup"
    submitText="Simpan Grup"
    maxWidth="max-w-md">

    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Nama Grup (Header Sidebar)</label>
        <input type="text" wire:model="groupName"
            class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 uppercase"
            placeholder="Contoh: TRANSAKSI">
        @error('groupName')
            <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Urutan Tampil</label>
        <input type="number" wire:model="groupOrder"
            class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500">
        @error('groupOrder')
            <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>

</x-modal-form>