<div
    x-data="inventuraLampLogin(@js($errors->any() || session()->has('error')))"
    :class="isOn ? 'is-lit overflow-y-auto lg:overflow-hidden' : 'overflow-hidden'"
    class="lamp-room relative h-dvh w-full flex flex-col justify-center px-4 py-4 sm:px-8"
>
    <!-- Lapisan Kunang-Kunang Emerald-Teal -->
    <div wire:ignore x-ref="fireflies" class="pointer-events-none fixed inset-0 z-0 overflow-hidden"></div>

    <!-- Ambient Glow Halus di Latar -->
    <div
        :class="isOn ? 'opacity-100 scale-100' : 'opacity-0 scale-90'"
        class="pointer-events-none fixed -top-32 left-1/4 w-[26rem] h-[26rem] rounded-full bg-teal-500/15 blur-[110px] transition-all duration-700 z-0">
    </div>
    <div
        :class="isOn ? 'opacity-100 scale-100' : 'opacity-0 scale-90'"
        class="pointer-events-none fixed -bottom-32 right-1/4 w-[24rem] h-[24rem] rounded-full bg-emerald-500/15 blur-[110px] transition-all duration-700 z-0">
    </div>

    <!-- Brand Watermark Pojok Kiri Atas -->
    <div class="fixed top-4 left-4 sm:top-6 sm:left-8 z-20 flex items-center gap-2.5 sm:gap-3 select-none">
        <div
            :class="isOn ? 'shadow-lg shadow-teal-500/30 border-white/25 opacity-100' : 'border-white/10 opacity-45'"
            class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-gradient-to-br from-teal-400 via-teal-500 to-emerald-600 flex items-center justify-center text-white border transition-all duration-500">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2.5L20 7v10l-8 4.5L4 17V7l8-4.5z" fill="currentColor" fill-opacity="0.16" />
                <path d="M12 11.5L20 7" />
                <path d="M12 11.5L4 7" />
                <path d="M12 11.5v10" />
            </svg>
        </div>
        <div :class="isOn ? 'opacity-100' : 'opacity-45'" class="flex flex-col transition-opacity duration-500">
            <span class="text-base sm:text-lg font-extrabold text-white tracking-tight leading-none">
                Inventura<span class="text-teal-400">.</span>
            </span>
            <span class="text-[9px] font-semibold uppercase tracking-[0.22em] text-teal-400/80 mt-1 leading-none">
                Asset &amp; Stock
            </span>
        </div>
    </div>

    <!-- Container Utama: Lampu Berdiri + Kartu Login (Terkunci Rapi Dalam 1 Layar) -->
    <div class="relative z-10 w-full max-w-5xl mx-auto my-auto flex flex-col lg:flex-row items-center justify-center gap-3 sm:gap-5 lg:gap-12">

        <!-- ==================== KIRI / ATAS: LAMPU BERDIRI INTERAKTIF ==================== -->
        <div
            wire:ignore
            :class="isOn ? 'lg:translate-x-0' : 'lg:translate-x-52'"
            class="relative flex flex-col items-center justify-center shrink-0 transition-all duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] select-none"
        >
            <svg
                x-ref="lampSvg"
                viewBox="0 0 280 500"
                :class="isOn
                    ? 'h-[20dvh] min-h-[125px] max-h-[165px] lg:h-[58dvh] lg:max-h-[420px]'
                    : 'h-[54dvh] min-h-[250px] max-h-[420px] lg:h-[60dvh] lg:max-h-[440px]'"
                class="lamp-svg-interactive w-auto overflow-visible drop-shadow-[0_24px_35px_rgba(0,0,0,0.65)] transition-all duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]"
            >
                <defs>
                    <linearGradient id="inventuraLightCone" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#5eead4" stop-opacity="0.42" />
                        <stop offset="35%" stop-color="#14b8a6" stop-opacity="0.18" />
                        <stop offset="80%" stop-color="#10b981" stop-opacity="0.04" />
                        <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                    </linearGradient>

                    <linearGradient id="shadeBody" x1="0" y1="0" x2="1" y2="0.4">
                        <stop offset="0%" stop-color="#16353a" />
                        <stop offset="45%" stop-color="#0d2226" />
                        <stop offset="100%" stop-color="#071417" />
                    </linearGradient>

                    <linearGradient id="poleMetal" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#1e3a3f" />
                        <stop offset="40%" stop-color="#335c63" />
                        <stop offset="100%" stop-color="#0c1d20" />
                    </linearGradient>

                    <radialGradient id="bulbGlowGrad" cx="50%" cy="45%" r="55%">
                        <stop offset="0%" stop-color="#f0fdfa" />
                        <stop offset="50%" stop-color="#5eead4" />
                        <stop offset="100%" stop-color="#14b8a6" />
                    </radialGradient>

                    <radialGradient id="floorGlowGrad" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.38" />
                        <stop offset="55%" stop-color="#10b981" stop-opacity="0.12" />
                        <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                    </radialGradient>

                    <radialGradient id="pullBeadGrad" cx="35%" cy="30%" r="65%">
                        <stop offset="0%" stop-color="#99f6e4" />
                        <stop offset="55%" stop-color="#14b8a6" />
                        <stop offset="100%" stop-color="#0f766e" />
                    </radialGradient>

                    <filter id="softBlur" x="-30%" y="-30%" width="160%" height="160%">
                        <feGaussianBlur stdDeviation="12" />
                    </filter>
                </defs>

                <!-- 1. Pantulan Cahaya di Lantai -->
                <ellipse cx="140" cy="466" rx="115" ry="22" fill="url(#floorGlowGrad)" class="lamp-floor-glow" />

                <!-- 2. Kerucut Cahaya dari Bawah Kap -->
                <polygon
                    points="64,142 216,142 290,468 -10,468"
                    fill="url(#inventuraLightCone)"
                    filter="url(#softBlur)"
                    class="lamp-light-cone"
                />

                <!-- 3. Tiang Lampu Berdiri & Alas -->
                <rect x="134" y="135" width="12" height="316" rx="6" fill="url(#poleMetal)" />
                <rect x="132" y="425" width="16" height="8" rx="3" fill="#2dd4bf" fill-opacity="0.35" />
                <ellipse cx="140" cy="462" rx="62" ry="13" fill="#061215" stroke="#1e4247" stroke-width="1.5" />
                <path d="M 92 456 Q 140 436 188 456 L 194 462 Q 140 472 86 462 Z" fill="url(#shadeBody)" stroke="#234c52" stroke-width="1" />

                <!-- 4. Bohlam -->
                <ellipse cx="140" cy="148" rx="18" ry="14" fill="#0f262a" stroke="#1f4247" stroke-width="1" />
                <ellipse cx="140" cy="148" rx="17" ry="13" fill="url(#bulbGlowGrad)" class="lamp-bulb-glow" />

                <!-- 5. Tali Saklar Elastis + Area Sentuh Lebar di Sepanjang Tali -->
                <path
                    x-ref="cordPath"
                    d="M 162 138 Q 162 182 162 226"
                    fill="none"
                    stroke="#64748b"
                    stroke-width="2.4"
                    stroke-linecap="round"
                    stroke-dasharray="3 2.5"
                />
                <path
                    x-ref="cordHitPath"
                    d="M 162 138 Q 162 182 162 226"
                    fill="none"
                    stroke="transparent"
                    stroke-width="32"
                    @pointerdown="onCordDown($event)"
                    class="cursor-grab active:cursor-grabbing"
                />

                <!-- Bandul Tarik + Area Sentuh Jari Luas (r=42) -->
                <g
                    x-ref="cordHandle"
                    transform="translate(162, 226)"
                    @pointerdown="onCordDown($event)"
                    class="cursor-grab active:cursor-grabbing"
                    role="button"
                    aria-label="Tarik tali saklar lampu"
                >
                    <circle cx="0" cy="6" r="42" fill="transparent" />
                    <circle cx="0" cy="6" r="11" fill="none" stroke="#2dd4bf" stroke-width="1.8" class="cord-hint-ring" />
                    <rect x="-2.5" y="-4" width="5" height="6" rx="2" fill="#94a3b8" />
                    <circle cx="0" cy="7" r="9.5" fill="url(#pullBeadGrad)" stroke="#ccfbf1" stroke-opacity="0.6" stroke-width="1.2" />
                    <circle cx="-3" cy="4" r="2.2" fill="#ffffff" fill-opacity="0.7" />
                </g>

                <!-- 6. Kap / Kubah Lampu -->
                <ellipse cx="140" cy="142" rx="78" ry="12" fill="#061417" stroke="#1d4247" stroke-width="1.2" />
                <ellipse cx="140" cy="142" rx="74" ry="9" fill="#2dd4bf" fill-opacity="0.38" class="lamp-shade-inner-glow" />
                <path
                    d="M 92 48 L 188 48 L 218 142 Q 140 156 62 142 Z"
                    fill="url(#shadeBody)"
                    stroke="#285860"
                    stroke-width="1.5"
                />
                <path
                    d="M 97 53 L 72 136"
                    stroke="#5eead4"
                    stroke-opacity="0.22"
                    stroke-width="2"
                    stroke-linecap="round"
                />
                <ellipse cx="140" cy="48" rx="48" ry="8" fill="#132e33" stroke="#285860" stroke-width="1.2" />
                <circle cx="140" cy="42" r="5" fill="#2dd4bf" fill-opacity="0.6" />
            </svg>

            <!-- Petunjuk Interaktif -->
            <div class="mt-2 text-center">
                <button
                    type="button"
                    @click="triggerQuickPull()"
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border text-xs font-medium transition-all duration-300 cursor-pointer"
                    :class="isOn
                        ? 'bg-white/[0.04] border-white/10 text-slate-400 hover:text-teal-300 hover:border-teal-500/30'
                        : 'bg-teal-500/10 border-teal-400/30 text-teal-300 shadow-[0_0_25px_rgba(20,184,166,0.2)] hover:bg-teal-500/20'"
                >
                    <span class="relative flex h-2 w-2">
                        <span x-show="!isOn" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" :class="isOn ? 'bg-emerald-400' : 'bg-teal-400'"></span>
                    </span>
                    <span x-text="isOn ? 'Tarik tali untuk matikan lampu' : 'Tarik tali lampu untuk masuk'"></span>
                </button>
            </div>
        </div>

        <!-- ==================== KANAN / BAWAH: KARTU LOGIN FROSTED GLASS ==================== -->
        <div
            :class="isOn
                ? 'opacity-100 translate-y-0 scale-100 pointer-events-auto max-h-[600px]'
                : 'opacity-0 translate-y-6 scale-95 pointer-events-none max-h-0 overflow-hidden'"
            class="w-full max-w-md transition-all duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]"
        >
            <div class="relative bg-white/[0.06] backdrop-blur-2xl border border-teal-400/20 rounded-3xl p-5 sm:p-7 shadow-[0_25px_70px_rgba(0,0,0,0.65),0_0_50px_rgba(20,184,166,0.12)]">

                <!-- Garis Cahaya Tipis di Puncak Kartu -->
                <div class="pointer-events-none absolute inset-x-10 -top-px h-px bg-gradient-to-r from-transparent via-teal-300/70 to-transparent"></div>

                <!-- Header Kartu Login -->
                <div class="mb-5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-teal-500/15 border border-teal-500/30 text-teal-300 text-[11px] font-semibold tracking-wide uppercase">
                        <x-heroicon-o-lock-closed class="w-3.5 h-3.5" />
                        Portal Autentikasi
                    </span>
                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight mt-2.5">
                        Masuk ke Inventura<span class="text-teal-400">.</span>
                    </h1>
                    <p class="text-slate-400 text-xs sm:text-sm mt-1">
                        Kelola stok, mutasi barang, dan laporan inventaris secara real-time.
                    </p>
                </div>

                @if (session()->has('error'))
                    <div class="mb-4 px-4 py-2.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs sm:text-sm flex items-center gap-2.5">
                        <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0 text-rose-400" />
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Form Login Livewire -->
                <form wire:submit="authenticate" class="space-y-3.5">

                    <!-- Email (tanpa autofocus agar keyboard HP tidak langsung muncul) -->
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Email Akun
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-heroicon-o-envelope class="w-5 h-5" />
                            </div>
                            <input
                                id="email"
                                type="email"
                                wire:model="email"
                                autocomplete="email"
                                placeholder="admin@inventura.test"
                                class="w-full bg-slate-950/55 border border-white/10 text-white rounded-xl pl-11 pr-4 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-500/25 transition-all placeholder:text-slate-500"
                            >
                        </div>
                        @error('email')
                            <p class="text-rose-400 text-xs mt-1 flex items-center gap-1">
                                <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-heroicon-o-key class="w-5 h-5" />
                            </div>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                wire:model="password"
                                autocomplete="current-password"
                                placeholder="Masukkan kata sandi"
                                class="w-full bg-slate-950/55 border border-white/10 text-white rounded-xl pl-11 pr-11 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-500/25 transition-all placeholder:text-slate-500"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-teal-300 transition-colors cursor-pointer"
                                title="Tampilkan / sembunyikan kata sandi"
                            >
                                <x-heroicon-o-eye x-show="!showPassword" class="w-5 h-5" />
                                <x-heroicon-o-eye-slash x-show="showPassword" style="display: none;" class="w-5 h-5" />
                            </button>
                        </div>
                        @error('password')
                            <p class="text-rose-400 text-xs mt-1 flex items-center gap-1">
                                <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Ingat Saya -->
                    <div class="flex items-center justify-between pt-0.5">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer select-none group">
                            <input
                                type="checkbox"
                                wire:model="remember"
                                class="checkbox checkbox-xs rounded border-white/20 bg-slate-900 checked:bg-teal-500 checked:border-teal-500"
                            >
                            <span class="text-xs sm:text-sm text-slate-400 group-hover:text-slate-200 transition-colors">
                                Ingat sesi saya
                            </span>
                        </label>
                    </div>

                    <!-- Tombol Masuk -->
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full mt-1 inline-flex items-center justify-center gap-2 px-6 py-2.5 sm:py-3 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-semibold shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:-translate-y-0.5 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed transition-all duration-300 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="authenticate" class="inline-flex items-center gap-2">
                            <span>Masuk ke Workspace</span>
                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                        </span>
                        <span wire:loading wire:target="authenticate" class="inline-flex items-center gap-2">
                            <span class="loading loading-spinner loading-xs"></span>
                            <span>Memverifikasi...</span>
                        </span>
                    </button>
                </form>

                <!-- Footer Mini Kartu -->
                <div class="mt-5 pt-3.5 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Inventura &copy; {{ date('Y') }}</span>
                    <span class="text-teal-400/75 font-medium">Glassmorphism Inventory OS</span>
                </div>
            </div>
        </div>

    </div>
</div>