<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Product;

class ReportIndex extends Component
{
    use WithPagination;

    public $startDate = '';
    public $endDate = '';
    public $filterType = '';
    public $filterProduct = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'filterType', 'filterProduct'])) {
            $this->resetPage();
        }
    }

    public function setPreset($preset)
    {
        if ($preset === 'today') {
            $this->startDate = now()->format('Y-m-d');
            $this->endDate   = now()->format('Y-m-d');
        } elseif ($preset === '7days') {
            $this->startDate = now()->subDays(6)->format('Y-m-d');
            $this->endDate   = now()->format('Y-m-d');
        } elseif ($preset === 'month') {
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
            $this->endDate   = now()->format('Y-m-d');
        }

        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->startDate     = now()->startOfMonth()->format('Y-m-d');
        $this->endDate       = now()->format('Y-m-d');
        $this->filterType    = '';
        $this->filterProduct = '';
        $this->resetPage();
    }

    public function render()
    {
        $baseQuery = Transaction::with(['product.category', 'supplier'])
            ->when($this->startDate, fn($q) => $q->whereRaw('DATE(COALESCE(transaction_date, created_at)) >= ?', [$this->startDate]))
            ->when($this->endDate, fn($q) => $q->whereRaw('DATE(COALESCE(transaction_date, created_at)) <= ?', [$this->endDate]))
            ->when($this->filterType !== '', fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterProduct !== '', fn($q) => $q->where('product_id', $this->filterProduct));

        // Hitung ringkasan periode dari seluruh data terfilter (bukan hanya halaman aktif)
        $totalTransactions = (clone $baseQuery)->count();
        $totalIn           = (clone $baseQuery)->where('type', 'in')->sum('quantity');
        $totalOut          = (clone $baseQuery)->where('type', 'out')->sum('quantity');

        $transactions = $baseQuery
            ->orderByRaw('COALESCE(transaction_date, created_at) DESC')
            ->latest('id')
            ->paginate(10);

        $exportParams = [
            'start_date' => $this->startDate,
            'end_date'   => $this->endDate,
            'type'       => $this->filterType,
            'product_id' => $this->filterProduct,
        ];

        return view('livewire.report-index', [
            'transactions'      => $transactions,
            'products'          => Product::orderBy('name')->get(),
            'totalTransactions' => $totalTransactions,
            'totalIn'           => $totalIn,
            'totalOut'          => $totalOut,
            'netMovement'       => $totalIn - $totalOut,
            'pdfUrl'            => route('laporan.pdf', $exportParams),
            'excelUrl'          => route('laporan.excel', $exportParams),
        ]);
    }
}