<div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-8 shadow-2xl">

    <!-- Brand Logo -->
    <div class="flex flex-col items-center text-center mb-8">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white font-extrabold text-2xl shadow-lg shadow-teal-500/30 mb-4">
            I
        </div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Inventura.</h1>
        <p class="text-sm text-slate-400 mt-1">Masuk untuk mengelola aset & inventaris gudang.</p>
    </div>

    <form wire:submit.prevent="authenticate" class="space-y-5">

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Alamat Email</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-heroicon-o-envelope class="w-5 h-5" />
                </div>
                <input type="email" wire:model="email" autofocus
                    class="w-full bg-slate-900/60 border border-white/10 text-white rounded-xl pl-11 pr-4 py-3 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all placeholder:text-slate-500"
                    placeholder="admin@inventura.test">
            </div>
            @error('email')
                <span class="text-rose-400 text-xs mt-1.5 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password dengan Toggle Lihat/Sembunyikan -->
        <div x-data="{ showPass: false }">
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-heroicon-o-lock-closed class="w-5 h-5" />
                </div>
                <input :type="showPass ? 'text' : 'password'" wire:model="password"
                    class="w-full bg-slate-900/60 border border-white/10 text-white rounded-xl pl-11 pr-11 py-3 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all placeholder:text-slate-500"
                    placeholder="••••••••">
                <button type="button" @click="showPass = !showPass"
                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-teal-400 transition-colors cursor-pointer">
                    <x-heroicon-o-eye x-show="!showPass" class="w-5 h-5" />
                    <x-heroicon-o-eye-slash x-show="showPass" x-cloak class="w-5 h-5" />
                </button>
            </div>
            @error('password')
                <span class="text-rose-400 text-xs mt-1.5 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Ingat Saya -->
        <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center gap-2 text-slate-400 cursor-pointer select-none">
                <input type="checkbox" wire:model="remember" class="checkbox checkbox-xs checkbox-success rounded" />
                <span>Ingat sesi saya</span>
            </label>
        </div>

        <!-- Tombol Login -->
        <button type="submit" wire:loading.attr="disabled"
            class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-semibold shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:-translate-y-0.5 active:scale-[0.99] transition-all duration-200 cursor-pointer disabled:opacity-60">
            <span wire:loading wire:target="authenticate" class="loading loading-spinner loading-sm"></span>
            <span>Masuk ke Workspace</span>
        </button>

    </form>

    <p class="text-center text-xs text-slate-500 mt-6">
        &copy; {{ date('Y') }} Inventura Inventory Management System
    </p>
</div>