<div class="p-6 flex flex-col gap-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/5 backdrop-blur-xl border border-white/10 p-5 lg:p-6 rounded-3xl shadow-lg">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">Manajemen Menu</h1>
            <p class="text-slate-400 text-sm lg:text-base mt-1">Atur struktur grup navigasi, menu utama, submenu, dan ikon sidebar secara
                dinamis.</p>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2.5 sm:gap-3 w-full md:w-auto shrink-0">
            <button type="button" wire:click="openGroupModal()"
                class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-5 py-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-200 text-[13px] sm:text-base font-semibold hover:bg-white/10 hover:border-teal-500/40 active:scale-95 transition-all duration-300 whitespace-nowrap cursor-pointer">
                <x-heroicon-o-folder-plus class="w-4 h-4 sm:w-5 sm:h-5 text-teal-400 shrink-0" />
                <span>Grup Menu</span>
            </button>

            <button type="button" wire:click="openMenuModal()"
                class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white text-[13px] sm:text-base font-semibold shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:-translate-y-1 active:scale-95 transition-all duration-300 whitespace-nowrap cursor-pointer">
                <x-heroicon-o-plus class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" />
                <span>Tambah Menu</span>
            </button>
        </div>
    </div>

    <!-- Daftar Grup & Struktur Menu -->
    <div class="space-y-6">
        @forelse ($menuGroups as $group)
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-lg">
                <!-- Header Grup -->
                <div class="flex flex-wrap items-center justify-between gap-4 pb-4 mb-4 border-b border-white/10">
                    <div class="flex items-center gap-3">
                        <span
                            class="px-3 py-1 rounded-xl bg-teal-500/15 border border-teal-500/30 text-teal-400 text-xs font-mono font-bold">
                            #{{ $group->order_no }}
                        </span>
                        <h2 class="text-lg font-bold text-white tracking-wider uppercase">{{ $group->name }}</h2>
                        <span class="text-xs text-slate-400 bg-white/5 px-2.5 py-1 rounded-lg">
                            {{ $group->menus->count() }} Menu Induk
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button wire:click="openMenuModal(null, {{ $group->id }})"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-500/10 border border-teal-500/20 text-teal-400 text-xs font-semibold hover:bg-teal-500 hover:text-white transition-all duration-300">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Menu di Grup Ini
                        </button>
                        <button wire:click="openGroupModal({{ $group->id }})"
                            class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500 hover:text-white hover:shadow-[0_0_15px_rgba(245,158,11,0.5)] transition-all duration-300"
                            title="Edit Grup">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                        </button>
                        <button type="button"
                            @click="$dispatch('open-delete-modal', { id: {{ $group->id }}, name: @js($group->name), action: 'deleteGroup', title: 'Hapus Grup Menu', note: 'Seluruh menu & submenu di dalam grup ini akan ikut terhapus.' })"
                            class="p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-600 hover:text-white hover:shadow-[0_0_15px_rgba(225,29,72,0.5)] transition-all duration-300 cursor-pointer"
                            title="Hapus Grup">
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Tabel Menu di dalam Grup -->
                <div class="overflow-x-auto">
                    <table class="table w-full text-slate-300">
                        <thead class="text-slate-400 border-b border-white/10 text-xs uppercase">
                            <tr>
                                <th class="w-16 text-center">URUTAN</th>
                                <th>NAMA MENU & IKON</th>
                                <th>ROUTE NAME</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($group->menus as $menu)
                                <!-- Baris Menu Induk -->
                                <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                    <td class="text-center font-mono text-xs text-slate-400">{{ $menu->order_no }}</td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-xl bg-white/5 border border-white/10 text-teal-400">
                                                @if ($menu->icon)
                                                    <x-dynamic-component :component="$menu->icon" class="w-5 h-5" />
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-semibold text-white">{{ $menu->name }}</p>
                                                @if ($menu->children->count() > 0)
                                                    <span
                                                        class="text-[11px] text-teal-400">{{ $menu->children->count() }}
                                                        Submenu</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($menu->route_name)
                                            <span
                                                class="font-mono text-xs px-2.5 py-1 rounded-lg bg-slate-900/60 border border-white/10 text-teal-300">
                                                {{ $menu->route_name }}
                                            </span>
                                        @else
                                            <span class="text-xs italic text-slate-500">Induk Dropdown (#)</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="toggleActive({{ $menu->id }})"
                                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer {{ $menu->is_active ? 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30' : 'bg-slate-700/50 text-slate-400 hover:bg-slate-700' }}">
                                            {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </td>
                                    <td class="text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button
                                                wire:click="openMenuModal(null, {{ $group->id }}, {{ $menu->id }})"
                                                class="px-2.5 py-1.5 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400 text-xs font-medium hover:bg-sky-500 hover:text-white transition-all duration-300"
                                                title="Tambah Submenu">
                                                + Sub
                                            </button>
                                            <button wire:click="openMenuModal({{ $menu->id }})"
                                                class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500 hover:text-white hover:shadow-[0_0_15px_rgba(245,158,11,0.5)] transition-all duration-300"
                                                title="Edit Menu">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                            </button>
                                            <button type="button"
                                                @click="$dispatch('open-delete-modal', { id: {{ $menu->id }}, name: @js($menu->name), action: 'deleteMenu', title: 'Hapus Menu' })"
                                                class="p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-600 hover:text-white hover:shadow-[0_0_15px_rgba(225,29,72,0.5)] transition-all duration-300 cursor-pointer"
                                                title="Hapus Menu">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Baris Submenu (Anak) -->
                                @foreach ($menu->children as $child)
                                    <tr
                                        class="border-b border-white/5 bg-white/[0.02] hover:bg-white/5 transition-colors">
                                        <td class="text-center font-mono text-xs text-slate-500">
                                            {{ $menu->order_no }}.{{ $child->order_no }}</td>
                                        <td>
                                            <div class="flex items-center gap-3 pl-8">
                                                <span class="text-slate-600">↳</span>
                                                <div
                                                    class="p-1.5 rounded-lg bg-white/5 border border-white/10 text-slate-300">
                                                    @if ($child->icon)
                                                        <x-dynamic-component :component="$child->icon" class="w-4 h-4" />
                                                    @endif
                                                </div>
                                                <span
                                                    class="text-sm font-medium text-slate-200">{{ $child->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="font-mono text-xs px-2.5 py-1 rounded-lg bg-slate-900/60 border border-white/10 text-sky-300">
                                                {{ $child->route_name ?? '#' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button wire:click="toggleActive({{ $child->id }})"
                                                class="px-3 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer {{ $child->is_active ? 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30' : 'bg-slate-700/50 text-slate-400 hover:bg-slate-700' }}">
                                                {{ $child->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </td>
                                        <td class="text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <button wire:click="openMenuModal({{ $child->id }})"
                                                    class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500 hover:text-white hover:shadow-[0_0_15px_rgba(245,158,11,0.5)] transition-all duration-300"
                                                    title="Edit Submenu">
                                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                </button>
                                                <button wire:click="deleteMenu({{ $child->id }})"
                                                    wire:confirm="Yakin ingin menghapus submenu '{{ $child->name }}'?"
                                                    class="p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-600 hover:text-white hover:shadow-[0_0_15px_rgba(225,29,72,0.5)] transition-all duration-300"
                                                    title="Hapus Submenu">
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-slate-500 text-sm">
                                        Belum ada menu di grup ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-10 text-center text-slate-400">
                Belum ada grup menu yang terdaftar. Silakan klik "+ Grup Menu" untuk memulai.
            </div>
        @endforelse
    </div>

    <!-- Alert Toast Auto-Hide 3 Detik -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-10"
            class="fixed top-5 right-5 bg-emerald-500/90 text-white px-6 py-3 rounded-2xl shadow-lg backdrop-blur-md z-50 flex items-center gap-3">
            <x-heroicon-o-check-circle class="w-6 h-6 animate-pulse" />
            {{ session('message') }}
        </div>
    @endif

    <!-- Modals (Terpisah di Partials agar kode utama tetap ringkas) -->
    @include('livewire.partials.menu-management.modal-group')
    @include('livewire.partials.menu-management.modal-menu')
    <x-modal-delete />

</div>
