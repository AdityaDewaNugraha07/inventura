<x-modal-form show="isRoleModalOpen"
    :title="$roleId ? 'Edit Role' : 'Tambah Role Baru'"
    submit="saveRole"
    :submitText="$roleId ? 'Update Role' : 'Simpan Role'"
    maxWidth="max-w-md">

    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Nama Role</label>
        <input type="text" wire:model="roleName"
            class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all"
            placeholder="Contoh: Kepala Gudang, Auditor">
        @error('roleName') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Deskripsi Tugas (Opsional)</label>
        <textarea wire:model="roleDescription" rows="3"
            class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all"
            placeholder="Jelaskan ruang lingkup wewenang role ini..."></textarea>
        @error('roleDescription') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
    </div>

</x-modal-form>