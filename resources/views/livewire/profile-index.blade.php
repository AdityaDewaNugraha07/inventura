<div class="p-6 flex flex-col gap-6">

    <!-- Header -->
    <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-lg flex items-center gap-5">
        <div class="w-16 h-16 rounded-2xl overflow-hidden border border-teal-500/30 shadow-[0_0_15px_rgba(20,184,166,0.25)] shrink-0">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D9488&color=fff&bold=true&size=128" alt="Avatar" class="w-full h-full object-cover" />
        </div>
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">{{ auth()->user()->name }}</h1>
            <p class="text-slate-400 text-sm mt-0.5">{{ auth()->user()->email }} &bull; <span class="text-teal-400 font-medium">{{ auth()->user()->role?->name ?? 'User' }}</span></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Kartu 1: Edit Informasi Profil -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-lg">
            <h2 class="text-lg font-bold text-white mb-1">Informasi Profil</h2>
            <p class="text-xs text-slate-400 mb-6">Perbarui nama tampilan dan alamat email akunmu.</p>

            <form wire:submit.prevent="updateProfile" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Nama Lengkap</label>
                    <input type="text" wire:model="name"
                        class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all">
                    @error('name') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Alamat Email</label>
                    <input type="email" wire:model="email"
                        class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all">
                    @error('email') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="pt-3 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-semibold shadow-lg shadow-teal-500/25 hover:shadow-teal-500/45 hover:-translate-y-0.5 transition-all cursor-pointer">
                        <span wire:loading wire:target="updateProfile" class="loading loading-spinner loading-xs"></span>
                        <span>Simpan Profil</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Kartu 2: Ubah Password -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-lg">
            <h2 class="text-lg font-bold text-white mb-1">Keamanan Password</h2>
            <p class="text-xs text-slate-400 mb-6">Gunakan password minimal 6 karakter untuk menjaga keamanan akun.</p>

            <form wire:submit.prevent="updatePassword" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Password Saat Ini</label>
                    <input type="password" wire:model="current_password"
                        class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all"
                        placeholder="••••••••">
                    @error('current_password') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Password Baru</label>
                    <input type="password" wire:model="password"
                        class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all"
                        placeholder="Minimal 6 karakter">
                    @error('password') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" wire:model="password_confirmation"
                        class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 transition-all"
                        placeholder="Ketik ulang password baru">
                </div>

                <div class="pt-3 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold shadow-lg shadow-amber-500/25 hover:shadow-amber-500/45 hover:-translate-y-0.5 transition-all cursor-pointer">
                        <span wire:loading wire:target="updatePassword" class="loading loading-spinner loading-xs"></span>
                        <span>Ubah Password</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Alert Toast -->
    @if (session()->has('message'))
        <div wire:key="toast-profile-{{ uniqid() }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
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

</div>