<x-modal-form show="isPermissionModalOpen"
    :title="'Hak Akses Menu — ' . $permissionRoleName"
    submit="savePermissions"
    submitText="Simpan Hak Akses"
    maxWidth="max-w-2xl">

    <div class="flex items-center justify-between gap-3 pb-3 border-b border-white/10">
        <p class="text-xs text-slate-400">
            Centang menu yang boleh dilihat dan diakses oleh role <span class="text-teal-400 font-semibold">{{ $permissionRoleName }}</span>.
        </p>
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" wire:click="selectAllMenus"
                class="px-3 py-1 rounded-lg bg-teal-500/15 border border-teal-500/30 text-teal-300 text-xs font-semibold hover:bg-teal-500 hover:text-white transition-colors cursor-pointer">
                Pilih Semua
            </button>
            <button type="button" wire:click="clearAllMenus"
                class="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-slate-400 text-xs font-semibold hover:text-white transition-colors cursor-pointer">
                Kosongkan
            </button>
        </div>
    </div>

    <div class="max-h-96 overflow-y-auto space-y-4 pr-1">
        @foreach ($menuGroups as $group)
            @if ($group->menus->count() > 0)
                <div class="p-4 rounded-2xl bg-slate-900/50 border border-white/10 space-y-3">
                    <h4 class="text-xs font-bold text-teal-400 uppercase tracking-widest">{{ $group->name }}</h4>

                    <div class="space-y-2.5">
                        @foreach ($group->menus as $menu)
                            @if ($menu->children->count() > 0)
                                <!-- Induk Dropdown + Daftar Submenu -->
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-slate-200">
                                        @if ($menu->icon)
                                            <x-dynamic-component :component="$menu->icon" class="w-4 h-4 text-teal-400" />
                                        @endif
                                        <span>{{ $menu->name }}</span>
                                        <span class="text-[11px] font-normal text-slate-500">(Submenu)</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-5">
                                        @foreach ($menu->children as $child)
                                            <label class="flex items-center gap-3 p-2.5 rounded-xl bg-white/[0.03] border border-white/5 hover:border-teal-500/40 cursor-pointer transition-colors">
                                                <input type="checkbox" wire:model="selectedMenuIds" value="{{ $child->id }}"
                                                    class="checkbox checkbox-sm checkbox-success rounded" />
                                                <div class="min-w-0">
                                                    <p class="text-sm font-medium text-white truncate">{{ $child->name }}</p>
                                                    <p class="text-[11px] font-mono text-slate-500 truncate">{{ $child->route_name }}</p>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <!-- Menu Tunggal -->
                                <label class="flex items-center gap-3 p-2.5 rounded-xl bg-white/[0.03] border border-white/5 hover:border-teal-500/40 cursor-pointer transition-colors">
                                    <input type="checkbox" wire:model="selectedMenuIds" value="{{ $menu->id }}"
                                        class="checkbox checkbox-sm checkbox-success rounded" />
                                    @if ($menu->icon)
                                        <x-dynamic-component :component="$menu->icon" class="w-4 h-4 text-slate-400 shrink-0" />
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-white truncate">{{ $menu->name }}</p>
                                        <p class="text-[11px] font-mono text-slate-500 truncate">{{ $menu->route_name }}</p>
                                    </div>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

</x-modal-form>