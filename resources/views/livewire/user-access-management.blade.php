<div class="p-6 flex flex-col gap-6">

    <!-- Header Section + Tab Switcher -->
    <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-5 lg:p-6 rounded-3xl shadow-lg flex flex-col gap-5">
        <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">Manajemen User &amp; Hak Akses</h1>
                <p class="text-slate-400 text-sm lg:text-base mt-1">Kelola akun pengguna, peran (role), dan batasan menu yang dapat diakses setiap role.</p>
            </div>

            <div class="w-full lg:w-auto shrink-0">
                @if ($activeTab === 'users')
                    <button type="button" wire:click="openUserModal()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-semibold shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:-translate-y-0.5 active:scale-95 transition-all duration-300 whitespace-nowrap cursor-pointer">
                        <x-heroicon-o-user-plus class="w-5 h-5 shrink-0" />
                        <span>Tambah User</span>
                    </button>
                @else
                    <button type="button" wire:click="openRoleModal()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-semibold shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:-translate-y-0.5 active:scale-95 transition-all duration-300 whitespace-nowrap cursor-pointer">
                        <x-heroicon-o-plus class="w-5 h-5 shrink-0" />
                        <span>Tambah Role</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Navigasi 2 Tab + Filter -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3.5 pt-4 border-t border-white/10">

            <!-- Segmented Tab Pill (Ramping 1 Baris di Semua Layar) -->
            <div class="grid grid-cols-2 sm:inline-flex w-full sm:w-auto p-1 rounded-2xl bg-slate-900/70 border border-white/10 gap-1">
                <button type="button" wire:click="setTab('users')"
                    class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 cursor-pointer {{ $activeTab === 'users' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30 shadow-[0_0_15px_rgba(20,184,166,0.18)]' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                    <x-heroicon-o-users class="w-4 h-4 shrink-0" />
                    <span>Daftar User</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[11px] bg-white/10 leading-none">{{ $users->total() }}</span>
                </button>

                <button type="button" wire:click="setTab('roles')"
                    class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 cursor-pointer {{ $activeTab === 'roles' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30 shadow-[0_0_15px_rgba(20,184,166,0.18)]' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                    <x-heroicon-o-shield-check class="w-4 h-4 shrink-0" />
                    <span class="sm:hidden">Role &amp; Akses</span>
                    <span class="hidden sm:inline">Role &amp; Hak Akses Menu</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[11px] bg-white/10 leading-none">{{ $roles->count() }}</span>
                </button>
            </div>

            <!-- Filter Khusus Tab User (Lebar Penuh & Sejajar Rapi) -->
            @if ($activeTab === 'users')
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 w-full xl:w-auto">
                    <div class="relative w-full sm:w-64">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama atau email..."
                            class="w-full bg-white/5 border border-white/10 text-white rounded-xl pl-10 pr-9 py-2.5 text-sm focus:outline-none focus:border-teal-500 transition-colors placeholder:text-slate-500">
                        @if ($search !== '')
                            <button type="button" wire:click="$set('search', '')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-white cursor-pointer">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        @endif
                    </div>

                    <select wire:model.live="filterRole"
                        class="w-full sm:w-48 bg-slate-900/80 border border-white/10 text-white text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 cursor-pointer">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    <!-- ==================== KONTEN TAB 1: DAFTAR USER ==================== -->
    @if ($activeTab === 'users')
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-lg flex flex-col">
            <div class="overflow-x-auto">
                <table class="table w-full text-slate-300">
                    <thead class="text-slate-400 border-b border-white/10 text-sm">
                        <tr>
                            <th>PENGGUNA</th>
                            <th>EMAIL LOGIN</th>
                            <th class="text-center">ROLE</th>
                            <th class="text-center">STATUS</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="whitespace-nowrap">
                                    <div class="flex items-center gap-3 pr-4">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D9488&color=fff&bold=true"
                                            alt="{{ $user->name }}"
                                            class="w-9 h-9 rounded-xl border border-teal-500/30 shrink-0">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-white">{{ $user->name }}</span>
                                                @if ($user->id === auth()->id())
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30 leading-none">
                                                        Kamu
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5">Bergabung {{ $user->created_at->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-mono text-sm text-slate-300 whitespace-nowrap">{{ $user->email }}</td>
                                <td class="text-center whitespace-nowrap">
                                    @if ($user->role?->is_super_admin)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-amber-500/15 border border-amber-500/25 text-amber-300 text-xs font-semibold">
                                            <x-heroicon-o-shield-check class="w-3.5 h-3.5" />
                                            {{ $user->role->name }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-teal-500/15 border border-teal-500/25 text-teal-300 text-xs font-semibold">
                                            {{ $user->role?->name ?? 'Belum Ada Role' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <button type="button" wire:click="toggleUserStatus({{ $user->id }})"
                                        class="px-3 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer {{ $user->is_active ? 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30' : 'bg-rose-500/20 text-rose-400 hover:bg-rose-500/30' }}"
                                        title="Klik untuk ubah status aktif">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        <button type="button" wire:click="openUserModal({{ $user->id }})"
                                            class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500 hover:text-white hover:shadow-[0_0_15px_rgba(245,158,11,0.5)] transition-all duration-300 cursor-pointer"
                                            title="Edit User">
                                            <x-heroicon-o-pencil-square class="w-5 h-5" />
                                        </button>

                                        @if ($user->id !== auth()->id())
                                            <button type="button"
                                                @click="$dispatch('open-delete-modal', {
                                                    id: {{ $user->id }},
                                                    name: @js($user->name . ' (' . $user->email . ')'),
                                                    action: 'deleteUser',
                                                    title: 'Hapus Akun User'
                                                })"
                                                class="p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-600 hover:text-white hover:shadow-[0_0_15px_rgba(225,29,72,0.5)] transition-all duration-300 cursor-pointer"
                                                title="Hapus User">
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-b border-white/5">
                                <td colspan="5" class="text-center py-8 text-slate-500">
                                    Tidak ada data user yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 border-t border-white/10 pt-4">
                {{ $users->links() }}
            </div>
        </div>
    @endif

    <!-- ==================== KONTEN TAB 2: ROLE & HAK AKSES ==================== -->
    @if ($activeTab === 'roles')
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($roles as $role)
                <div class="bg-white/5 backdrop-blur-xl border {{ $role->is_super_admin ? 'border-amber-500/30' : 'border-white/10' }} rounded-3xl p-6 shadow-lg flex flex-col justify-between gap-5 hover:border-teal-500/40 transition-all duration-300">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 rounded-2xl {{ $role->is_super_admin ? 'bg-amber-500/15 border border-amber-500/30 text-amber-400' : 'bg-teal-500/15 border border-teal-500/30 text-teal-400' }}">
                                    <x-heroicon-o-shield-check class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white leading-tight">{{ $role->name }}</h3>
                                    <span class="font-mono text-xs text-slate-500">{{ $role->slug }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" wire:click="openRoleModal({{ $role->id }})"
                                    class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500 hover:text-white transition-all cursor-pointer"
                                    title="Edit Role">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </button>

                                @if (!$role->is_super_admin)
                                    <button type="button"
                                        @click="$dispatch('open-delete-modal', {
                                            id: {{ $role->id }},
                                            name: @js($role->name),
                                            action: 'deleteRole',
                                            title: 'Hapus Role'
                                        })"
                                        class="p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-600 hover:text-white transition-all cursor-pointer"
                                        title="Hapus Role">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </div>

                        <p class="text-sm text-slate-400 mt-4 leading-relaxed">
                            {{ $role->description ?? 'Tidak ada deskripsi untuk role ini.' }}
                        </p>

                        <!-- Statistik Role -->
                        <div class="grid grid-cols-2 gap-3 mt-5">
                            <div class="p-3 rounded-2xl bg-white/[0.03] border border-white/5">
                                <p class="text-xs text-slate-500">Jumlah User</p>
                                <p class="text-lg font-bold text-white mt-0.5">{{ $role->users_count }} <span class="text-xs font-normal text-slate-400">Akun</span></p>
                            </div>
                            <div class="p-3 rounded-2xl bg-white/[0.03] border border-white/5">
                                <p class="text-xs text-slate-500">Akses Menu</p>
                                <p class="text-lg font-bold text-teal-400 mt-0.5">
                                    @if ($role->is_super_admin)
                                        Semua
                                    @else
                                        {{ $role->menus_count }} <span class="text-xs font-normal text-slate-400">Menu</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Atur Hak Akses Menu -->
                    <div class="pt-4 border-t border-white/10">
                        @if ($role->is_super_admin)
                            <div class="w-full py-2.5 px-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs font-semibold text-center">
                                Akses Penuh (Full Bypass)
                            </div>
                        @else
                            <button type="button" wire:click="openPermissionModal({{ $role->id }})"
                                class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-teal-500/15 border border-teal-500/30 text-teal-300 text-sm font-semibold hover:bg-teal-500 hover:text-white hover:shadow-[0_0_15px_rgba(20,184,166,0.35)] transition-all duration-200 cursor-pointer">
                                <x-heroicon-o-key class="w-4 h-4" />
                                <span>Atur Hak Akses Menu</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Alert Toast Sukses -->
    @if (session()->has('message'))
        <div wire:key="toast-ok-{{ uniqid() }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-10"
            class="fixed top-5 right-5 bg-emerald-500/90 text-white px-6 py-3 rounded-2xl shadow-lg backdrop-blur-md z-50 flex items-center gap-3">
            <x-heroicon-o-check-circle class="w-6 h-6 animate-pulse" />
            {{ session('message') }}
        </div>
    @endif

    <!-- Alert Toast Error -->
    @if (session()->has('error'))
        <div wire:key="toast-err-{{ uniqid() }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4500)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-10"
            class="fixed top-5 right-5 bg-rose-600/95 text-white px-6 py-3 rounded-2xl shadow-lg backdrop-blur-md z-50 flex items-center gap-3">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6 animate-pulse" />
            {{ session('error') }}
        </div>
    @endif

    <!-- Partials Modal -->
    @include('livewire.partials.user-access.modal-user')
    @include('livewire.partials.user-access.modal-role')
    @include('livewire.partials.user-access.modal-permission')
    <x-modal-delete />

</div>