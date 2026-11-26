<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Supplier;

class TransactionIndex extends Component
{
    use WithPagination;

    // Mode halaman: 'all', 'in' (Barang Masuk), atau 'out' (Barang Keluar)
    public $mode = 'all';
    public $search = '';
    public $filterType = '';

    // Variabel Form Modal Transaksi
    public $isModalOpen = false;
    public $type = 'in';
    public $product_id = '';
    public $supplier_id = '';
    public $quantity = 1;
    public $transaction_date = '';
    public $notes = '';

    public function mount()
    {
        if (request()->routeIs('transaksi.masuk')) {
            $this->mode = 'in';
            $this->type = 'in';
        } elseif (request()->routeIs('transaksi.keluar')) {
            $this->mode = 'out';
            $this->type = 'out';
        } else {
            $this->mode = 'all';
            $this->type = 'in';
        }

        $this->transaction_date = now()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function openModal($type = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($type) {
            $this->type = $type;
        } elseif ($this->mode !== 'all') {
            $this->type = $this->mode;
        } else {
            $this->type = 'in';
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function resetForm()
    {
        $this->product_id       = '';
        $this->supplier_id      = '';
        $this->quantity         = 1;
        $this->transaction_date = now()->format('Y-m-d');
        $this->notes            = '';
    }

    public function store()
    {
        $this->validate([
            'type'             => 'required|in:in,out',
            'product_id'       => 'required|exists:products,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'quantity'         => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($this->product_id);

        // Proteksi agar stok tidak pernah minus saat Barang Keluar
        if ($this->type === 'out' && $this->quantity > $product->stock) {
            $this->addError('quantity', "Stok tidak mencukupi! Sisa stok {$product->name} saat ini hanya {$product->stock} Unit.");
            return;
        }

        DB::transaction(function () use ($product) {
            $prefix = $this->type === 'in' ? 'TRX-IN' : 'TRX-OUT';
            $dateCode = now()->format('Ymd');
            $todayCount = Transaction::whereDate('created_at', today())->count() + 1;
            $referenceCode = $prefix . '-' . $dateCode . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT);

            // Pastikan kode referensi selalu unik meski ada transaksi yang pernah dihapus
            while (Transaction::where('reference_code', $referenceCode)->exists()) {
                $todayCount++;
                $referenceCode = $prefix . '-' . $dateCode . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT);
            }

            Transaction::create([
                'reference_code'   => $referenceCode,
                'product_id'       => $this->product_id,
                'supplier_id'      => $this->type === 'in' ? ($this->supplier_id ?: null) : null,
                'type'             => $this->type,
                'quantity'         => $this->quantity,
                'transaction_date' => $this->transaction_date,
                'notes'            => trim($this->notes) ?: null,
            ]);

            // Update stok barang otomatis
            if ($this->type === 'in') {
                $product->increment('stock', $this->quantity);
            } else {
                $product->decrement('stock', $this->quantity);
            }
        });

        $label = $this->type === 'in' ? 'Barang masuk' : 'Barang keluar';
        $this->closeModal();
        $this->resetForm();
        session()->flash('message', "{$label} berhasil dicatat dan stok telah diperbarui!");
    }

    public function delete($id)
    {
        $transaction = Transaction::with('product')->findOrFail($id);
        $product = $transaction->product;

        // Cegah hapus transaksi masuk jika stok yang masuk sudah terlanjur terpakai/keluar
        if ($transaction->type === 'in' && $product && $product->stock < $transaction->quantity) {
            session()->flash('error', "Gagal menghapus! Stok {$product->name} saat ini ({$product->stock}) lebih kecil dari jumlah masuk ({$transaction->quantity}).");
            return;
        }

        DB::transaction(function () use ($transaction, $product) {
            if ($product) {
                if ($transaction->type === 'in') {
                    $product->decrement('stock', $transaction->quantity);
                } else {
                    $product->increment('stock', $transaction->quantity);
                }
            }
            $transaction->delete();
        });

        session()->flash('message', 'Transaksi berhasil dihapus dan stok barang telah dikembalikan!');
    }

    public function render()
    {
        $keyword = trim($this->search);

        $transactions = Transaction::with(['product', 'supplier'])
            ->when($this->mode !== 'all', fn($q) => $q->where('type', $this->mode))
            ->when($this->mode === 'all' && $this->filterType !== '', fn($q) => $q->where('type', $this->filterType))
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('reference_code', 'like', '%' . $keyword . '%')
                      ->orWhere('notes', 'like', '%' . $keyword . '%')
                      ->orWhereHas('product', fn($p) => $p->where('name', 'like', '%' . $keyword . '%')->orWhere('sku', 'like', '%' . $keyword . '%'))
                      ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', '%' . $keyword . '%'));
                });
            })
            ->latest()
            ->paginate(10);

        $selectedProduct = $this->product_id ? Product::find($this->product_id) : null;

        return view('livewire.transaction-index', [
            'transactions'    => $transactions,
            'products'        => Product::orderBy('name')->get(),
            'suppliers'       => Supplier::orderBy('name')->get(),
            'selectedProduct' => $selectedProduct,
        ]);
    }
}