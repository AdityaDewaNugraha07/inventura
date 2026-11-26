<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\ProductIndex;
use App\Livewire\CategoryIndex;
use App\Livewire\SupplierIndex;
use App\Livewire\TransactionIndex;
use App\Livewire\MenuManagement;
use App\Livewire\ProfileIndex;
use App\Livewire\ReportIndex;
use App\Http\Controllers\ReportExportController;
use App\Livewire\UserAccessManagement;

// Halaman Khusus Tamu (Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Halaman Internal Aplikasi (Wajib Login)
Route::middleware(['auth', 'menu.access'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/barang', ProductIndex::class)->name('barang');
    Route::get('/kategori', CategoryIndex::class)->name('kategori');
    Route::get('/supplier', SupplierIndex::class)->name('supplier');

    Route::get('/transaksi', TransactionIndex::class)->name('transaksi.index');
    Route::get('/transaksi/masuk', TransactionIndex::class)->name('transaksi.masuk');
    Route::get('/transaksi/keluar', TransactionIndex::class)->name('transaksi.keluar');

    Route::get('/laporan', ReportIndex::class)->name('laporan.index');
    Route::get('/laporan/export-pdf', [ReportExportController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/export-excel', [ReportExportController::class, 'exportExcel'])->name('laporan.excel');

    Route::get('/settings/users-access', UserAccessManagement::class)->name('setting.user-access');
    Route::get('/settings/menus', MenuManagement::class)->name('setting.menu');
    Route::get('/profil', ProfileIndex::class)->name('profil');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});