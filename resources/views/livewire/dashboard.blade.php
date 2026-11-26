<div class="p-6 flex flex-col gap-6">

    @php
        $hourWib = (int) \Carbon\Carbon::now('Asia/Jakarta')->format('H');
        $initialGreeting = match (true) {
            $hourWib >= 4 && $hourWib < 11  => 'Selamat Pagi',
            $hourWib >= 11 && $hourWib < 15 => 'Selamat Siang',
            $hourWib >= 15 && $hourWib < 18 => 'Selamat Sore',
            default                         => 'Selamat Malam',
        };

        $healthColors = match ($healthStatus) {
            'optimal'  => [
                'badge' => 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300',
                'bar'   => 'from-teal-400 to-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.65)]',
                'text'  => 'text-emerald-400',
            ],
            'warning'  => [
                'badge' => 'bg-amber-500/15 border-amber-500/30 text-amber-300',
                'bar'   => 'from-amber-400 to-orange-400 shadow-[0_0_12px_rgba(251,191,36,0.6)]',
                'text'  => 'text-amber-400',
            ],
            'critical' => [
                'badge' => 'bg-rose-500/15 border-rose-500/30 text-rose-300',
                'bar'   => 'from-rose-500 to-pink-500 shadow-[0_0_12px_rgba(244,63,94,0.65)]',
                'text'  => 'text-rose-400',
            ],
            default    => [
                'badge' => 'bg-slate-500/15 border-white/10 text-slate-400',
                'bar'   => 'from-slate-500 to-slate-400',
                'text'  => 'text-slate-400',
            ],
        };
    @endphp

    <!-- Hero Command Header -->
    <div
        x-data="{
            greeting: '{{ $initialGreeting }}',
            mobileDate: '{{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d M Y') }}',
            mobileTime: '{{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i') }}',
            timer: null,
            syncTime() {
                const now = new Date();
                const hour = parseInt(new Intl.DateTimeFormat('en-GB', {
                    hour: '2-digit',
                    hour12: false,
                    timeZone: 'Asia/Jakarta'
                }).format(now), 10);

                if (hour >= 4 && hour < 11) this.greeting = 'Selamat Pagi';
                else if (hour >= 11 && hour < 15) this.greeting = 'Selamat Siang';
                else if (hour >= 15 && hour < 18) this.greeting = 'Selamat Sore';
                else this.greeting = 'Selamat Malam';

                this.mobileDate = new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    timeZone: 'Asia/Jakarta'
                }).format(now);

                this.mobileTime = new Intl.DateTimeFormat('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                    timeZone: 'Asia/Jakarta'
                }).format(now).replace(/\./g, ':');
            },
            init() {
                this.syncTime();
                this.timer = setInterval(() => this.syncTime(), 15000);
            },
            destroy() {
                clearInterval(this.timer);
            }
        }"
        class="relative overflow-hidden bg-white/5 backdrop-blur-xl border border-white/10 p-5 lg:p-7 rounded-3xl shadow-lg"
    >
        <!-- Ambient Glow Halus di Latar Kartu -->
        <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-teal-500/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-16 w-64 h-64 rounded-full bg-emerald-500/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">

            <!-- Kiri: Status, Role, Sapaan Dinamis & Tanggal Mobile -->
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/15 border border-teal-500/30 text-teal-300 text-xs font-semibold">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-400"></span>
                        </span>
                        Sistem Online
                    </span>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/[0.04] border border-white/10 text-slate-300 text-xs font-medium">
                        <x-heroicon-o-shield-check class="w-3.5 h-3.5 text-teal-400 shrink-0" />
                        {{ auth()->user()->role?->name ?? 'User' }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight mt-3.5 leading-snug">
                    <span x-text="greeting"></span>,
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-300 to-emerald-300">
                        {{ auth()->user()->name }}
                    </span>
                </h1>

                <p class="text-slate-400 text-sm sm:text-base mt-1.5 max-w-xl">
                    Pantau pergerakan aset, kesehatan stok gudang, dan aktivitas inventaris hari ini secara real-time.
                </p>

                <!-- Info Tanggal & Jam WIB khusus Smartphone (karena di navbar mobile disembunyikan) -->
                <div class="md:hidden inline-flex items-center gap-2 mt-3.5 px-3 py-1.5 rounded-xl bg-slate-900/55 border border-white/10 text-xs text-slate-300">
                    <x-heroicon-o-clock class="w-3.5 h-3.5 text-teal-400 shrink-0" />
                    <span x-text="mobileDate"></span>
                    <span class="text-white/20">&bull;</span>
                    <span class="font-mono text-teal-300 font-semibold" x-text="mobileTime + ' WIB'"></span>
                </div>
            </div>

            <!-- Kanan: Mini Widget Kesehatan Inventaris (Inset Glass Pill) -->
            <div class="w-full lg:w-80 xl:w-96 shrink-0 p-4 sm:p-5 rounded-2xl bg-slate-900/55 border border-white/10 shadow-inner backdrop-blur-md">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Kesehatan Stok
                    </span>
                    <span class="px-2.5 py-0.5 rounded-lg border text-[11px] font-semibold {{ $healthColors['badge'] }}">
                        {{ $healthLabel }}
                    </span>
                </div>

                <div class="flex items-baseline justify-between gap-3 mt-3">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-extrabold tracking-tight {{ $healthColors['text'] }}">
                            {{ $stockHealthPercent }}%
                        </span>
                        <span class="text-xs text-slate-400">aman</span>
                    </div>
                    <span class="text-xs font-medium text-slate-300 font-mono">
                        {{ $safeStockCount }}/{{ $totalProducts }} SKU
                    </span>
                </div>

                <!-- Glowing Progress Bar -->
                <div class="w-full h-2 rounded-full bg-white/10 mt-3 overflow-hidden">
                    <div
                        class="h-full rounded-full bg-gradient-to-r {{ $healthColors['bar'] }} transition-all duration-700 liquid-ease"
                        style="width: {{ $stockHealthPercent }}%">
                    </div>
                </div>

                <!-- Keterangan Bawah -->
                <div class="mt-3 pt-2.5 border-t border-white/5 flex items-center justify-between gap-2 text-xs">
                    @if ($totalProducts === 0)
                        <span class="text-slate-400">Belum ada SKU barang terdaftar di sistem.</span>
                    @elseif ($lowStockCount > 0)
                        <span class="inline-flex items-center gap-1.5 text-rose-300">
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 text-rose-400" />
                            <span><strong>{{ $lowStockCount }} SKU</strong> perlu segera restock</span>
                        </span>
                        @if (auth()->user()?->hasMenuAccess('transaksi.masuk'))
                            <a href="{{ route('transaksi.masuk') }}" wire:navigate
                                class="text-teal-300 hover:text-teal-200 font-semibold whitespace-nowrap transition-colors">
                                Restock &rarr;
                            </a>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1.5 text-emerald-300/90">
                            <x-heroicon-o-check-circle class="w-4 h-4 shrink-0 text-emerald-400" />
                            <span>Semua stok di atas batas aman (&gt; 10 unit)</span>
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- 4 Kartu Metrik Utama (KPI Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <!-- Total Stok Tersedia -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-5 rounded-3xl shadow-lg hover:border-teal-500/30 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2.5 rounded-2xl bg-sky-500/15 border border-sky-500/20 text-sky-400">
                    <x-heroicon-o-cube class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium px-2.5 py-1 rounded-lg bg-teal-500/15 text-teal-400">
                    Aktif
                </span>
            </div>
            <p class="text-sm text-slate-400">Total Stok Tersedia</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-bold text-white">{{ number_format($totalStock) }}</span>
                <span class="text-sm text-slate-500">Unit</span>
            </div>
        </div>

        <!-- Total Jenis Barang / SKU -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-5 rounded-3xl shadow-lg hover:border-emerald-500/30 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/20 text-emerald-400">
                    <x-heroicon-o-squares-2x2 class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium px-2.5 py-1 rounded-lg bg-emerald-500/15 text-emerald-400">
                    {{ $totalCategories }} Kategori
                </span>
            </div>
            <p class="text-sm text-slate-400">Total Master Barang</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-bold text-white">{{ number_format($totalProducts) }}</span>
                <span class="text-sm text-slate-500">SKU</span>
            </div>
        </div>

        <!-- Peringatan Stok Menipis -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-5 rounded-3xl shadow-lg hover:border-rose-500/30 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2.5 rounded-2xl bg-rose-500/15 border border-rose-500/20 text-rose-400">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                </div>
                @if ($lowStockCount > 0)
                    <span class="text-xs font-medium px-2.5 py-1 rounded-lg bg-rose-500/15 text-rose-400">
                        Perlu Restock
                    </span>
                @else
                    <span class="text-xs font-medium px-2.5 py-1 rounded-lg bg-emerald-500/15 text-emerald-400">
                        Stok Aman
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-400">Peringatan Stok Menipis</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-bold text-white">{{ number_format($lowStockCount) }}</span>
                <span class="text-sm text-slate-500">Item</span>
            </div>
        </div>

        <!-- Transaksi Hari Ini -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-5 rounded-3xl shadow-lg hover:border-amber-500/30 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2.5 rounded-2xl bg-amber-500/15 border border-amber-500/20 text-amber-400">
                    <x-heroicon-o-arrows-right-left class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium px-2.5 py-1 rounded-lg bg-amber-500/15 text-amber-400">
                    Hari Ini
                </span>
            </div>
            <p class="text-sm text-slate-400">Transaksi Masuk/Keluar</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-3xl font-bold text-white">{{ number_format($todayTransactions) }}</span>
                <span class="text-sm text-slate-500">Aktivitas</span>
            </div>
        </div>

    </div>

    <!-- Area Konten Bawah (Split 2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kiri: Daftar Barang Terbaru -->
        <div class="lg:col-span-7 bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-lg flex flex-col">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-white">Inventaris Terbaru</h2>
                    <p class="text-xs text-slate-400 mt-0.5">5 data barang terakhir yang ditambahkan ke sistem.</p>
                </div>
                <a href="{{ route('barang') }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-400 hover:text-teal-300 transition-colors">
                    Lihat Semua
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full text-slate-300">
                    <thead class="text-slate-400 border-b border-white/10 text-xs uppercase">
                        <tr>
                            <th>SKU</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th class="text-right">Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentProducts as $item)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="font-mono text-xs text-teal-400 whitespace-nowrap">{{ $item->sku }}</td>
                                <td class="font-medium text-white">{{ $item->name }}</td>
                                <td class="text-slate-400 text-sm">{{ $item->category?->name ?? 'Kategori #' . $item->category_id }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <span class="inline-flex items-center whitespace-nowrap px-2.5 py-1 rounded-lg text-xs font-medium {{ $item->stock > 10 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                        {{ $item->stock }} Unit
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-slate-500 text-sm">
                                    Belum ada data barang yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kanan: Monitor Peringatan Stok Menipis -->
        <div class="lg:col-span-5 bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-lg flex flex-col">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-white">Monitor Stok Kritis</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Barang dengan sisa stok &le; 10 unit yang perlu restock.</p>
                </div>
                <div class="p-2 rounded-xl bg-rose-500/10 text-rose-400">
                    <x-heroicon-o-bell class="w-5 h-5" />
                </div>
            </div>

            <div class="flex-1 flex flex-col justify-center gap-3">
                @forelse ($lowStockProducts as $lowItem)
                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-white/[0.03] border border-white/5 hover:border-rose-500/30 transition-colors">
                        <div>
                            <p class="font-medium text-white text-sm">{{ $lowItem->name }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $lowItem->sku }} &bull; {{ $lowItem->category?->name ?? '-' }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-xl bg-rose-500/20 text-rose-400 text-xs font-bold">
                            Sisa {{ $lowItem->stock }}
                        </span>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center text-center py-8 px-4 rounded-2xl border border-dashed border-white/10 bg-white/[0.02]">
                        <div class="p-3 rounded-2xl bg-emerald-500/15 text-emerald-400 mb-3">
                            <x-heroicon-o-check-circle class="w-7 h-7" />
                        </div>
                        <p class="text-sm font-semibold text-white">Semua Stok Dalam Kondisi Aman</p>
                        <p class="text-xs text-slate-400 mt-1">Tidak ada barang dengan stok di bawah batas minimum saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>