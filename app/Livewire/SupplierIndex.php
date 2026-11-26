<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Supplier;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class SupplierIndex extends Component
{
    use WithPagination;

    public $search = '';

    // Variabel Form Tambah & Edit Supplier
    public $supplierId = null;
    public $code = '';
    public $name = '';
    public $phone = '';
    public $email = '';
    public $address = '';
    public $isModalOpen = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();

        if ($id) {
            $supplier = Supplier::findOrFail($id);
            $this->supplierId = $supplier->id;
            $this->code       = $supplier->code;
            $this->name       = $supplier->name;
            $this->phone      = $supplier->phone ?? '';
            $this->email      = $supplier->email ?? '';
            $this->address    = $supplier->address ?? '';
        } else {
            $this->resetForm();
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function resetForm()
    {
        $this->supplierId = null;
        $this->code       = '';
        $this->name       = '';
        $this->phone      = '';
        $this->email      = '';
        $this->address    = '';
    }

    public function store()
    {
        $this->validate([
            'code'    => ['required', 'string', 'max:30', Rule::unique('suppliers', 'code')->ignore($this->supplierId)],
            'name'    => 'required|string|min:2|max:150',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
        ]);

        $isEdit = (bool) $this->supplierId;

        Supplier::updateOrCreate(
            ['id' => $this->supplierId],
            [
                'code'    => strtoupper(trim($this->code)),
                'name'    => trim($this->name),
                'phone'   => trim($this->phone) ?: null,
                'email'   => trim($this->email) ?: null,
                'address' => trim($this->address) ?: null,
            ]
        );

        $this->closeModal();
        $this->resetForm();
        session()->flash('message', $isEdit ? 'Data supplier berhasil diperbarui!' : 'Supplier baru berhasil ditambahkan!');
    }

    public function delete($id)
    {
        Supplier::findOrFail($id)->delete();
        session()->flash('message', 'Data supplier berhasil dihapus!');
    }

    public function render()
    {
        $keyword = trim($this->search);

        $suppliers = Supplier::when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('code', 'like', '%' . $keyword . '%')
                      ->orWhere('name', 'like', '%' . $keyword . '%')
                      ->orWhere('phone', 'like', '%' . $keyword . '%')
                      ->orWhere('email', 'like', '%' . $keyword . '%')
                      ->orWhere('address', 'like', '%' . $keyword . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.supplier-index', [
            'suppliers' => $suppliers,
        ]);
    }
}