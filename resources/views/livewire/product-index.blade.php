<div class="p-6 h-full flex flex-col gap-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between md:items-end gap-4 bg-white/5 backdrop-blur-xl border border-white/10 p-5 lg:p-6 rounded-3xl shadow-lg">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">Master Data Barang</h1>
            <p class="text-slate-400 text-sm lg:text-base mt-1">Kelola daftar inventaris, kategori, dan stok aset.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- Kotak Pencarian Reaktif -->
            <div class="relative w-full sm:w-64 lg:w-72">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                </div>

                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama, SKU, atau kategori..."
                    class="w-full bg-white/5 border border-white/10 text-white rounded-xl pl-10 pr-9 py-2.5 focus:outline-none focus:border-teal-500 transition-colors placeholder:text-slate-500">

                <!-- Loading Indicator saat mencari -->
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <span class="loading loading-spinner loading-xs text-teal-400"></span>
                </div>

                <!-- Tombol Clear Search (muncul otomatis jika ada teks) -->
                @if ($search !== '')
                    <button type="button" wire:click="$set('search', '')" wire:loading.remove wire:target="search"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-white transition-colors cursor-pointer"
                        title="Hapus pencarian">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                @endif
            </div>

            <button type="button" wire:click="openModal()"
                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-semibold shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:-translate-y-1 active:scale-95 transition-all duration-300 whitespace-nowrap cursor-pointer">
                <x-heroicon-o-plus class="w-5 h-5 shrink-0" />
                <span>Tambah Barang</span>
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="flex-1 bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-lg overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="table w-full text-slate-300">
                <thead class="text-slate-400 border-b border-white/10 text-sm">
                    <tr>
                        <th class="text-center whitespace-nowrap">SKU / KODE</th>
                        <th class="text-center whitespace-nowrap">NAMA BARANG</th>
                        <th class="text-center whitespace-nowrap">KATEGORI</th>
                        <th class="text-center whitespace-nowrap">STOK</th>
                        <th class="text-center whitespace-nowrap">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $item)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="text-center font-mono text-teal-400 whitespace-nowrap">{{ $item->sku }}</td>
                            <td class="text-center font-medium text-white whitespace-nowrap">{{ $item->name }}</td>
                            <td class="text-center text-slate-400 whitespace-nowrap">{{ $item->category?->name ?? $item->category_id }}
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <span
                                    class="px-3 py-1 rounded-lg {{ $item->stock > 10 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                    {{ $item->stock }} Unit
                                </span>
                            </td>
                            <td class="text-center whitespace-nowrap">

                                <!-- Tombol Edit (Kuning/Amber) -->
                                <button type="button" wire:click="openModal({{ $item->id }})"
                                    class="group p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500 hover:text-white hover:shadow-[0_0_15px_rgba(245,158,11,0.5)] transition-all duration-300 cursor-pointer"
                                    title="Edit Barang">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                </button>

                                <!-- Tombol Hapus (Merah/Rose) -->
                                <button type="button"
                                    @click="$dispatch('open-delete-modal', { id: {{ $item->id }}, name: @js($item->name), action: 'delete', title: 'Hapus Data Barang' })"
                                    class="group p-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-600 hover:text-white hover:shadow-[0_0_15px_rgba(225,29,72,0.5)] transition-all duration-300 cursor-pointer"
                                    title="Hapus Barang">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>

                            </td>
                        </tr>
                    @empty
                        <tr class="border-b border-white/5">
                            <td colspan="5" class="text-center py-8 text-slate-500">
                                @if (trim($search) !== '')
                                    Tidak ada barang yang cocok dengan kata kunci "<span
                                        class="text-teal-400 font-medium">{{ $search }}</span>".
                                @else
                                    Belum ada data barang. Silakan klik "Tambah Barang" untuk memulai.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4 border-t border-white/10 pt-4">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Alert Notifikasi Berhasil (Alpine.js untuk Auto-Hide 3 Detik) -->
    @if (session()->has('message'))
        <div wire:key="toast-{{ uniqid() }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-10"
            class="fixed top-5 right-5 bg-emerald-500/90 text-white px-6 py-3 rounded-2xl shadow-lg backdrop-blur-md z-50 flex items-center gap-3">
            <x-heroicon-o-check-circle class="w-6 h-6 animate-pulse" />
            {{ session('message') }}
        </div>
    @endif

    <!-- MODAL TAMBAH BARANG (Reusable Component) -->
    <x-modal-form show="isModalOpen" :title="$productId ? 'Edit Data Barang' : 'Tambah Barang Baru'" submit="store" :submitText="$productId ? 'Update Barang' : 'Simpan Barang'">

        <!-- Input SKU -->
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">SKU / Kode Barang</label>
            <input type="text" wire:model="sku"
                class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                placeholder="Contoh: BRG-001">
            @error('sku')
                <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Input Nama -->
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Nama Barang</label>
            <input type="text" wire:model="name"
                class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                placeholder="Masukkan nama barang">
            @error('name')
                <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Input Kategori (Dropdown Dinamis) -->
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">Kategori</label>
                <select wire:model="category_id"
                    class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Input Stok -->
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">Stok Awal</label>
                <input type="number" wire:model="stock"
                    class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                    placeholder="0">
                @error('stock')
                    <span class="text-rose-400 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>

    </x-modal-form>

    <!-- MODAL DELETE UNIVERSAL -->
    <x-modal-delete />

</div>
