<x-modal-form show="isUserModalOpen"
    :title="$userId ? 'Edit Akun User' : 'Tambah Akun User Baru'"
    submit="saveUser"
    :submitText="$userId ? 'Update User' : 'Simpan User'">

    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Nama Lengkap</label>
        <input type="text" wire:model="name"
            class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all"
            placeholder="Contoh: Budi Santoso">
        @error('name') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Email Login</label>
            <input type="email" wire:model="email"
                class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all"
                placeholder="budi@inventura.test">
            @error('email') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Role / Peran</label>
            <select wire:model="role_id"
                class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all">
                <option value="">-- Pilih Role --</option>
                @foreach ($roles as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                @endforeach
            </select>
            @error('role_id') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">
                Password {{ $userId ? '(Kosongkan jika tetap)' : '' }}
            </label>
            <input type="password" wire:model="password"
                class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all"
                placeholder="Minimal 6 karakter">
            @error('password') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Konfirmasi Password</label>
            <input type="password" wire:model="password_confirmation"
                class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all"
                placeholder="Ketik ulang password">
        </div>
    </div>

    <div class="flex items-center gap-3 pt-1">
        <input type="checkbox" wire:model="is_active" id="user_is_active" class="toggle toggle-success toggle-sm" />
        <label for="user_is_active" class="text-sm text-slate-300 cursor-pointer">Akun aktif dan diizinkan login</label>
    </div>
    @error('is_active') <span class="text-rose-400 text-xs block">{{ $message }}</span> @enderror

</x-modal-form>