<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;

class Dashboard extends Component
{
    public function render()
    {
        $totalStock = Product::sum('stock');
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $lowStockCount = Product::where('stock', '<=', 10)->count();
        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        // Hitung persentase kesehatan stok (SKU dengan stok aman > 10 unit)
        $safeStockCount = max(0, $totalProducts - $lowStockCount);

        if ($totalProducts === 0) {
            $stockHealthPercent = 0;
            $healthStatus       = 'empty';
            $healthLabel        = 'Belum Ada Data';
        } else {
            $stockHealthPercent = (int) round(($safeStockCount / $totalProducts) * 100);

            if ($stockHealthPercent >= 80) {
                $healthStatus = 'optimal';
                $healthLabel  = 'Kondisi Optimal';
            } elseif ($stockHealthPercent >= 50) {
                $healthStatus = 'warning';
                $healthLabel  = 'Perlu Perhatian';
            } else {
                $healthStatus = 'critical';
                $healthLabel  = 'Stok Kritis';
            }
        }

        $recentProducts = Product::with('category')
            ->latest()
            ->take(5)
            ->get();

        $lowStockProducts = Product::with('category')
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        return view('livewire.dashboard', [
            'totalStock' => $totalStock,
            'totalProducts' => $totalProducts,
            'totalCategories' => $totalCategories,
            'lowStockCount' => $lowStockCount,
            'todayTransactions' => $todayTransactions,
            'recentProducts' => $recentProducts,
            'lowStockProducts' => $lowStockProducts,
            'safeStockCount'     => $safeStockCount,
            'stockHealthPercent' => $stockHealthPercent,
            'healthStatus'       => $healthStatus,
            'healthLabel'        => $healthLabel,
        ]);
    }
}