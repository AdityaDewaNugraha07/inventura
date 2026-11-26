<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\Category;

class ProductIndex extends Component
{
    use WithPagination;

    public $search = '';

    // Variabel untuk Form Tambah & Edit Barang
    public $productId = null;
    public $name, $sku, $category_id, $stock = 0;
    public $isModalOpen = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Fungsi membuka modal (kosong = Tambah, ada $id = Edit)
    public function openModal($id = null)
    {
        $this->resetValidation();

        if ($id) {
            $product = Product::findOrFail($id);
            $this->productId   = $product->id;
            $this->sku         = $product->sku;
            $this->name        = $product->name;
            $this->category_id = $product->category_id;
            $this->stock       = $product->stock;
        } else {
            $this->resetForm();
        }

        $this->isModalOpen = true;
    }

    // Fungsi menutup modal
    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    // Fungsi mereset isian form
    public function resetForm()
    {
        $this->productId   = null;
        $this->name        = '';
        $this->sku         = '';
        $this->category_id = '';
        $this->stock       = 0;
    }

    // Fungsi menyimpan data (otomatis Create atau Update)
    public function store()
    {
        $this->validate([
            'name'        => 'required|min:3',
            'sku'         => ['required', Rule::unique('products', 'sku')->ignore($this->productId)],
            'category_id' => 'required|exists:categories,id',
            'stock'       => 'required|numeric|min:0',
        ]);

        $isEdit = (bool) $this->productId;

        Product::updateOrCreate(
            ['id' => $this->productId],
            [
                'name'        => $this->name,
                'sku'         => $this->sku,
                'category_id' => $this->category_id,
                'stock'       => $this->stock,
            ]
        );

        $this->closeModal();
        $this->resetForm();
        session()->flash('message', $isEdit ? 'Data barang berhasil diperbarui!' : 'Barang berhasil ditambahkan!');
    }

    // Fungsi hapus data (dipanggil oleh <x-modal-delete />)
    public function delete($id)
    {
        Product::findOrFail($id)->delete();
        session()->flash('message', 'Barang berhasil dihapus!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $keyword = trim($this->search);

        $products = Product::with('category')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('sku', 'like', '%' . $keyword . '%')
                        ->orWhereHas('category', function ($cat) use ($keyword) {
                            $cat->where('name', 'like', '%' . $keyword . '%');
                        });
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.product-index', [
            'products'   => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
