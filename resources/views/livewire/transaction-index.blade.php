<div class="p-6 h-full flex flex-col gap-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between md:items-end gap-4 bg-white/5 backdrop-blur-xl border border-white/10 p-5 lg:p-6 rounded-3xl shadow-lg">
        <div class="min-w-0">
            <h1 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">
                @if ($mode === 'in')
                    Transaksi Barang Masuk
                @elseif ($mode === 'out')
                    Transaksi Barang Keluar
                @else
                    Riwayat Transaksi Stok
                @endif
            </h1>
            <p class="text-slate-400 text-sm lg:text-base mt-1">
                @if ($mode === 'in')
                    Catat penerimaan barang dari supplier untuk menambah stok inventaris secara otomatis.
                @elseif ($mode === 'out')
                    Catat pengeluaran atau pemakaian barang untuk mengurangi stok secara real-time.
                @else
                    Pantau seluruh mutasi barang masuk dan keluar beserta referensi transaksinya.
                @endif
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- Kotak Pencarian Reaktif -->
            <div class="relative w-full sm:w-64 lg:w-72">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                </div>

                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode, barang, catatan..."
                    class="w-full bg-white/5 border border-white/10 text-white rounded-xl pl-10 pr-9 py-2.5 focus:outline-none focus:border-teal-500 transition-colors placeholder:text-slate-500">

                <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <span class="loading loading-spinner loading-xs text-teal-400"></span>
                </div>

                @if ($search !== '')
                    <button type="button" wire:click="$set('search', '')" wire:loading.remove wire:target="search"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-white transition-colors cursor-pointer"
                        title="Hapus pencarian">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                @endif
            </div>

            <!-- Filter Jenis (khusus halaman Riwayat Transaksi) -->
            @if ($mode === 'all')
                <select wire:model.live="filterType"
                    class="bg-slate-900/70 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 cursor-pointer">
                    <option value="">Semua Jenis</option>
                    <option value="in">Barang Masuk</option>
                    <option value="out">Barang Keluar</option>
                </select>
            @endif

            <!-- Tombol Catat Masuk (HANYA di halaman Barang Masuk) -->
            @if ($mode === 'in')
                <button type="button" wire:click="openModal('in')"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-semibold shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:-translate-y-1 active:scale-95 transition-all duration-300 whitespace-nowrap cursor-pointer">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5 shrink-0" />
                    <span>Barang Masuk</span>
                </button>
            @endif

            <!-- Tombol Catat Keluar (HANYA di halaman Barang Keluar) -->
            @if ($mode === 'out')
                <button type="button" wire:click="openModal('out')"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-rose-500 text-white font-semibold shadow-lg shadow-rose-500/25 hover:shadow-rose-500/45 hover:-translate-y-1 active:scale-95 transition-all duration-300 whitespace-nowrap cursor-pointer">
                    <x-heroicon-o-arrow-up-tray class="w-5 h-5 shrink-0" />
                    <span>Barang Keluar</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Table Section -->
    <div
        class="flex-1 bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-lg overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="table w-full text-slate-300">
                <thead class="text-slate-400 border-b border-white/10 text-sm">
                    <tr>
                        <th class="whitespace-nowrap">KODE & TANGGAL</th>
                        <th class="text-center whitespace-nowrap">JENIS</th>
                        <th>BARANG</th>
                        @if ($mode !== 'out')
                            <th>SUPPLIER</th>
                        @endif
                        <th class="text-center whitespace-nowrap">JUMLAH</th>
                        <th>CATATAN</th>
                        @if ($mode !== 'all')
                            <th class="text-center whitespace-nowrap">AKSI</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $trx)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="whitespace-nowrap">
                                <p class="font-mono text-xs text-teal-400 font-semibold">
                                    {{ $trx->reference_code ?? '#TRX-' . $trx->id }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ ($trx->transaction_date ?? $trx->created_at)->format('d M Y') }}
                                </p>
                            </td>
                            <td class="text-center whitespace-nowrap">
                                @if ($trx->type === 'in')
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 text-xs font-semibold">
                                        <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                        Masuk
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-500/20 text-rose-400 text-xs font-semibold">
                                        <x-heroicon-o-arrow-up-tray class="w-3.5 h-3.5" />
                                        Keluar
                                    </span>
                                @endif
                            </td>
                            <td>
                                <p class="font-medium text-white">{{ $trx->product?->name ?? 'Barang Dihapus' }}</p>
                                <p class="font-mono text-xs text-slate-400">{{ $trx->product?->sku ?? '-' }}</p>
                            </td>
                            @if ($mode !== 'out')
                                <td class="text-sm text-slate-300">
                                    {{ $trx->supplier?->name ?? '-' }}
                                </td>
                            @endif
                            <td class="text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-lg font-bold text-sm {{ $trx->type === 'in' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-rose-500/15 text-rose-400' }}">
                                    {{ $trx->type === 'in' ? '+' : '-' }}{{ number_format($trx->quantity) }} Unit
                                </span>
                            </td>
                            <td class="text-sm text-slate-400 max-w-xs">{{ $trx->notes ?? '-' }}</td>
                            @if ($mode !== 'all')
                                <td class="text-center whitespace-nowrap">
                                    <button type="button"
                                        @click="$dispatch('open-delete-modal', {
                                        id: {{ $trx->id }},
                                        name: @js(($trx->reference_code ?? 'Transaksi #' . $trx->id) . ' (' . ($trx->product?->name ?? '-') . ')'),
                                        action: 'delete',
                                        title: 'Hapus & Batalkan Transaksi',
                                        note: 'Menghapus transaksi ini akan otomatis mengembalikan (rollback) stok barang ke jumlah sebelumnya.'
                                    })"
                                        class="group p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-600 hover:text-white hover:shadow-[0_0_15px_rgba(225,29,72,0.5)] transition-all duration-300 cursor-pointer"
                                        title="Hapus & Rollback Stok">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr class="border-b border-white/5">
                            <td colspan="{{ $mode !== 'in' ? 7 : 6 }}" class="text-center py-8 text-slate-500">
                                @if (trim($search) !== '')
                                    Tidak ada transaksi yang cocok dengan kata kunci "<span
                                        class="text-teal-400 font-medium">{{ $search }}</span>".
                                @else
                                    Belum ada riwayat transaksi yang tercatat.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4 border-t border-white/10 pt-4">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Alert Notifikasi Berhasil -->
    @if (session()->has('message'))
        <div wire:key="toast-success-{{ uniqid() }}" x-data="{ show: true }" x-show="show"
            x-init="setTimeout(() => show = false, 3000)" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-10"
            class="fixed top-5 right-5 bg-emerald-500/90 text-white px-6 py-3 rounded-2xl shadow-lg backdrop-blur-md z-50 flex items-center gap-3">
            <x-heroicon-o-check-circle class="w-6 h-6 animate-pulse" />
            {{ session('message') }}
        </div>
    @endif

    <!-- Alert Notifikasi Error (misal rollback gagal) -->
    @if (session()->has('error'))
        <div wire:key="toast-error-{{ uniqid() }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4500)"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-10"
            class="fixed top-5 right-5 bg-rose-600/95 text-white px-6 py-3 rounded-2xl shadow-lg backdrop-blur-md z-50 flex items-center gap-3">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6 animate-pulse" />
            {{ session('error') }}
        </div>
    @endif

    <!-- MODAL CATAT TRANSAKSI (Reusable Component) -->
    <x-modal-form show="isModalOpen" :title="$type === 'in' ? 'Catat Barang Masuk' : 'Catat Barang Keluar'" submit="store" :submitText="$type === 'in' ? 'Simpan Barang Masuk' : 'Simpan Barang Keluar'">

        <!-- Pilih Barang -->
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Pilih Barang</label>
            <select wire:model.live="product_id"
                class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
                <option value="">-- Pilih Barang --</option>
                @foreach ($products as $prod)
                    <option value="{{ $prod->id }}">{{ $prod->sku }} — {{ $prod->name }} (Stok:
                        {{ $prod->stock }})</option>
                @endforeach
            </select>
            @error('product_id')
                <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
            @enderror

            <!-- Info Stok Real-Time Barang Terpilih -->
            @if ($selectedProduct)
                <div
                    class="mt-2 flex items-center justify-between px-3.5 py-2 rounded-xl bg-white/[0.04] border border-white/10 text-xs">
                    <span class="text-slate-400">Stok tersedia saat ini:</span>
                    <span class="font-bold {{ $selectedProduct->stock > 10 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ number_format($selectedProduct->stock) }} Unit
                    </span>
                </div>
            @endif
        </div>

        <!-- Pilih Supplier (Hanya tampil jika Barang Masuk) -->
        @if ($type === 'in')
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">Supplier / Pemasok (Opsional)</label>
                <select wire:model="supplier_id"
                    class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
                    <option value="">-- Tanpa Supplier / Internal --</option>
                    @foreach ($suppliers as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->code }} — {{ $sup->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id')
                    <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Jumlah -->
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">
                    Jumlah {{ $type === 'in' ? 'Masuk' : 'Keluar' }} (Unit)
                </label>
                <input type="number" min="1" wire:model="quantity"
                    class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                    placeholder="1">
                @error('quantity')
                    <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Tanggal Transaksi -->
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">Tanggal Transaksi</label>
                <input type="date" wire:model="transaction_date"
                    class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
                @error('transaction_date')
                    <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Catatan -->
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Catatan / Keterangan (Opsional)</label>
            <textarea wire:model="notes" rows="3"
                class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                placeholder="{{ $type === 'in' ? 'Contoh: Pengiriman PO #012 dari supplier...' : 'Contoh: Dipakai divisi operasional / penjualan...' }}"></textarea>
            @error('notes')
                <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

    </x-modal-form>

    <!-- MODAL DELETE UNIVERSAL -->
    <x-modal-delete />

</div>
