<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class CategoryIndex extends Component
{
    use WithPagination;

    public $search = '';

    // Variabel Form Tambah & Edit Kategori
    public $categoryId = null;
    public $name = '';
    public $description = '';
    public $isModalOpen = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();

        if ($id) {
            $category = Category::findOrFail($id);
            $this->categoryId  = $category->id;
            $this->name        = $category->name;
            $this->description = $category->description ?? '';
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
        $this->categoryId  = null;
        $this->name        = '';
        $this->description = '';
    }

    public function store()
    {
        $this->validate([
            'name'        => ['required', 'string', 'min:2', 'max:100', Rule::unique('categories', 'name')->ignore($this->categoryId)],
            'description' => 'nullable|string|max:255',
        ]);

        $isEdit = (bool) $this->categoryId;

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            [
                'name'        => $this->name,
                'description' => $this->description ?: null,
            ]
        );

        $this->closeModal();
        $this->resetForm();
        session()->flash('message', $isEdit ? 'Kategori berhasil diperbarui!' : 'Kategori baru berhasil ditambahkan!');
    }

    public function delete($id)
    {
        Category::findOrFail($id)->delete();
        session()->flash('message', 'Kategori berhasil dihapus!');
    }

    public function render()
    {
        $keyword = trim($this->search);

        $categories = Category::withCount('products')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.category-index', [
            'categories' => $categories,
        ]);
    }
}