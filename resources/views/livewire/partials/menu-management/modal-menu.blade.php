<!-- MODAL TAMBAH / EDIT MENU & SUBMENU + ICON PICKER -->
<x-modal-form show="isMenuModalOpen"
    :title="$menuId ? 'Edit Menu' : 'Tambah Menu / Submenu Baru'"
    submit="saveMenu"
    submitText="Simpan Menu"
    maxWidth="max-w-2xl">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Pilih Grup -->
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Grup Sidebar</label>
            <select wire:model.live="menu_group_id"
                class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500">
                <option value="">-- Pilih Grup --</option>
                @foreach ($menuGroups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
            @error('menu_group_id')
                <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Pilih Menu Induk (Opsional jika Submenu) -->
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Induk Menu (Kosongkan jika Menu Utama)</label>
            <select wire:model="parent_id"
                class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500">
                <option value="">-- Jadikan Menu Utama --</option>
                @foreach ($parentOptions as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                @endforeach
            </select>
            @error('parent_id')
                <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Nama Menu -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-400 mb-1">Nama Menu</label>
            <input type="text" wire:model="name"
                class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500"
                placeholder="Contoh: Barang Masuk">
            @error('name')
                <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Urutan -->
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Urutan</label>
            <input type="number" wire:model="order_no"
                class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500">
            @error('order_no')
                <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Route Name -->
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Nama Route Laravel (Opsional untuk Induk Dropdown)</label>
        <input type="text" wire:model="route_name"
            class="w-full bg-slate-900/50 border border-white/10 text-white font-mono text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500"
            placeholder="Contoh: transaksi.masuk">
        @error('route_name')
            <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- VISUAL ICON PICKER -->
    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-slate-400">Pilih Ikon Menu</label>
            <span class="text-xs font-mono text-teal-400 bg-teal-500/10 px-2.5 py-1 rounded-lg border border-teal-500/20">
                {{ $icon }}
            </span>
        </div>

        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 max-h-48 overflow-y-auto p-3 rounded-2xl bg-slate-900/60 border border-white/10">
            @foreach ($availableIcons as $iconKey => $iconLabel)
                <button type="button" wire:click="selectIcon('{{ $iconKey }}')"
                    class="flex flex-col items-center justify-center gap-1 p-2.5 rounded-xl border transition-all duration-200 cursor-pointer {{ $icon === $iconKey ? 'bg-teal-500/20 border-teal-400 text-teal-300 shadow-[0_0_12px_rgba(20,184,166,0.3)]' : 'bg-white/[0.02] border-white/5 text-slate-400 hover:bg-white/10 hover:text-white' }}">
                    <x-dynamic-component :component="$iconKey" class="w-5 h-5" />
                    <span class="text-[10px] truncate w-full text-center">{{ $iconLabel }}</span>
                </button>
            @endforeach
        </div>
        @error('icon')
            <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- Status Aktif -->
    <div class="flex items-center gap-3 pt-1">
        <input type="checkbox" wire:model="is_active" id="is_active" class="toggle toggle-success toggle-sm" />
        <label for="is_active" class="text-sm text-slate-300 cursor-pointer">Tampilkan menu ini di sidebar</label>
    </div>

</x-modal-form>