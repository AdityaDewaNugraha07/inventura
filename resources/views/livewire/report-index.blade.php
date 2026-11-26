<div class="p-6 flex flex-col gap-6">

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-4 bg-white/5 backdrop-blur-xl border border-white/10 p-5 lg:p-6 rounded-3xl shadow-lg">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">Laporan Mutasi Stok</h1>
            <p class="text-slate-400 text-sm lg:text-base mt-1">Filter pergerakan barang per periode dan ekspor ke format PDF atau Excel.</p>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2.5 sm:gap-3 w-full md:w-auto shrink-0">
            <a href="{{ $excelUrl }}"
                class="inline-flex items-center justify-center sm:flex-1 lg:flex-none gap-2 px-6 py-2.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 font-semibold hover:bg-emerald-500 hover:text-white hover:shadow-[0_0_18px_rgba(16,185,129,0.4)] active:scale-95 transition-all duration-300 whitespace-nowrap cursor-pointer">
                <x-heroicon-o-table-cells class="w-5 h-5 shrink-0" />
                <span>Export Excel</span>
            </a>

            <a href="{{ $pdfUrl }}" target="_blank"
                class="inline-flex items-center justify-center sm:flex-1 lg:flex-none gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white font-semibold shadow-lg shadow-rose-500/25 hover:shadow-rose-500/45 hover:-translate-y-0.5 active:scale-95 transition-all duration-300 whitespace-nowrap cursor-pointer">
                <x-heroicon-o-document-arrow-down class="w-5 h-5 shrink-0" />
                <span>Cetak PDF</span>
            </a>
        </div>
    </div>

    <!-- Panel Filter Periode & Preset Cepat -->
    <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-lg space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm font-semibold text-white">
                <x-heroicon-o-funnel class="w-4 h-4 text-teal-400" />
                <span>Filter Laporan</span>
            </div>

            <!-- Preset Tanggal Cepat -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="setPreset('today')"
                    class="px-3 py-1.5 rounded-xl text-xs font-medium bg-white/5 border border-white/10 text-slate-300 hover:border-teal-500/40 hover:text-teal-300 transition-colors cursor-pointer">
                    Hari Ini
                </button>
                <button type="button" wire:click="setPreset('7days')"
                    class="px-3 py-1.5 rounded-xl text-xs font-medium bg-white/5 border border-white/10 text-slate-300 hover:border-teal-500/40 hover:text-teal-300 transition-colors cursor-pointer">
                    7 Hari Terakhir
                </button>
                <button type="button" wire:click="setPreset('month')"
                    class="px-3 py-1.5 rounded-xl text-xs font-medium bg-white/5 border border-white/10 text-slate-300 hover:border-teal-500/40 hover:text-teal-300 transition-colors cursor-pointer">
                    Bulan Ini
                </button>
                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-800/80 border border-white/10 text-slate-400 hover:text-white transition-colors cursor-pointer"
                    title="Reset semua filter">
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    Reset
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Tanggal Mulai -->
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Dari Tanggal</label>
                <input type="date" wire:model.live="startDate"
                    class="w-full bg-slate-900/60 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-500 transition-colors">
            </div>

            <!-- Tanggal Akhir -->
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Sampai Tanggal</label>
                <input type="date" wire:model.live="endDate"
                    class="w-full bg-slate-900/60 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-500 transition-colors">
            </div>

            <!-- Jenis Transaksi -->
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Jenis Mutasi</label>
                <select wire:model.live="filterType"
                    class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-500 transition-colors cursor-pointer">
                    <option value="">Semua Mutasi</option>
                    <option value="in">Barang Masuk</option>
                    <option value="out">Barang Keluar</option>
                </select>
            </div>

            <!-- Filter Barang -->
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Spesifik Barang</label>
                <select wire:model.live="filterProduct"
                    class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-500 transition-colors cursor-pointer">
                    <option value="">Semua Barang</option>
                    @foreach ($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->sku }} — {{ $prod->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- 4 Kartu Ringkasan Periode Terpilih -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-5 rounded-3xl shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2.5 rounded-2xl bg-sky-500/15 border border-sky-500/20 text-sky-400">
                    <x-heroicon-o-arrows-right-left class="w-5 h-5" />
                </div>
                <span class="text-xs text-slate-400">Periode Terpilih</span>
            </div>
            <p class="text-xs text-slate-400">Total Transaksi</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($totalTransactions) }} <span class="text-sm font-normal text-slate-500">Aktivitas</span></p>
        </div>

        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-5 rounded-3xl shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/20 text-emerald-400">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                </div>
                <span class="text-xs font-medium text-emerald-400">Inbound</span>
            </div>
            <p class="text-xs text-slate-400">Total Barang Masuk</p>
            <p class="text-2xl font-bold text-emerald-400 mt-1">+{{ number_format($totalIn) }} <span class="text-sm font-normal text-slate-500">Unit</span></p>
        </div>

        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-5 rounded-3xl shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2.5 rounded-2xl bg-rose-500/15 border border-rose-500/20 text-rose-400">
                    <x-heroicon-o-arrow-up-tray class="w-5 h-5" />
                </div>
                <span class="text-xs font-medium text-rose-400">Outbound</span>
            </div>
            <p class="text-xs text-slate-400">Total Barang Keluar</p>
            <p class="text-2xl font-bold text-rose-400 mt-1">-{{ number_format($totalOut) }} <span class="text-sm font-normal text-slate-500">Unit</span></p>
        </div>

        <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-5 rounded-3xl shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2.5 rounded-2xl bg-teal-500/15 border border-teal-500/20 text-teal-400">
                    <x-heroicon-o-scale class="w-5 h-5" />
                </div>
                <span class="text-xs text-slate-400">Net Mutasi</span>
            </div>
            <p class="text-xs text-slate-400">Selisih Bersih</p>
            <p class="text-2xl font-bold mt-1 {{ $netMovement >= 0 ? 'text-teal-400' : 'text-amber-400' }}">
                {{ $netMovement >= 0 ? '+' : '' }}{{ number_format($netMovement) }} <span class="text-sm font-normal text-slate-500">Unit</span>
            </p>
        </div>
    </div>

    <!-- Tabel Preview Laporan -->
    <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-lg flex flex-col">
        <div class="overflow-x-auto">
            <table class="table w-full text-slate-300">
                <thead class="text-slate-400 border-b border-white/10 text-sm">
                    <tr>
                        <th class="whitespace-nowrap">KODE &amp; TANGGAL</th>
                        <th class="text-center whitespace-nowrap">JENIS</th>
                        <th>BARANG</th>
                        <th>KATEGORI</th>
                        <th>SUPPLIER</th>
                        <th class="text-right whitespace-nowrap">JUMLAH</th>
                        <th>CATATAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $trx)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="whitespace-nowrap">
                                <p class="font-mono text-xs text-teal-400 font-semibold">{{ $trx->reference_code ?? '#TRX-' . $trx->id }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ ($trx->transaction_date ?? $trx->created_at)->format('d M Y') }}</p>
                            </td>
                            <td class="text-center whitespace-nowrap">
                                @if ($trx->type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 text-xs font-semibold">
                                        <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                        Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-500/20 text-rose-400 text-xs font-semibold">
                                        <x-heroicon-o-arrow-up-tray class="w-3.5 h-3.5" />
                                        Keluar
                                    </span>
                                @endif
                            </td>
                            <td>
                                <p class="font-medium text-white">{{ $trx->product?->name ?? 'Barang Dihapus' }}</p>
                                <p class="font-mono text-xs text-slate-400">{{ $trx->product?->sku ?? '-' }}</p>
                            </td>
                            <td class="text-sm text-slate-400">{{ $trx->product?->category?->name ?? '-' }}</td>
                            <td class="text-sm text-slate-300">{{ $trx->supplier?->name ?? '-' }}</td>
                            <td class="text-right whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg font-bold text-sm {{ $trx->type === 'in' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-rose-500/15 text-rose-400' }}">
                                    {{ $trx->type === 'in' ? '+' : '-' }}{{ number_format($trx->quantity) }} Unit
                                </span>
                            </td>
                            <td class="text-sm text-slate-400 max-w-xs">{{ $trx->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr class="border-b border-white/5">
                            <td colspan="7" class="text-center py-8 text-slate-500">
                                Tidak ada data mutasi stok pada periode filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 border-t border-white/10 pt-4">
            {{ $transactions->links() }}
        </div>
    </div>

</div>