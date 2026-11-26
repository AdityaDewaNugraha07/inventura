<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportExportController extends Controller
{
    private function getFilteredQuery(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->query('end_date', now()->format('Y-m-d'));
        $type      = $request->query('type', '');
        $productId = $request->query('product_id', '');

        return Transaction::with(['product.category', 'supplier'])
            ->whereRaw('DATE(COALESCE(transaction_date, created_at)) >= ?', [$startDate])
            ->whereRaw('DATE(COALESCE(transaction_date, created_at)) <= ?', [$endDate])
            ->when($type !== '', fn($q) => $q->where('type', $type))
            ->when($productId !== '', fn($q) => $q->where('product_id', $productId))
            ->orderByRaw('COALESCE(transaction_date, created_at) DESC')
            ->latest('id');
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->query('end_date', now()->format('Y-m-d'));
        $type      = $request->query('type', '');
        $productId = $request->query('product_id', '');

        $transactions = $this->getFilteredQuery($request)->get();
        $totalIn      = $transactions->where('type', 'in')->sum('quantity');
        $totalOut     = $transactions->where('type', 'out')->sum('quantity');
        $productName  = $productId ? (Product::find($productId)?->name ?? 'Semua Barang') : 'Semua Barang';

        $pdf = Pdf::loadView('reports.transactions-pdf', [
            'transactions' => $transactions,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'type'         => $type,
            'productName'  => $productName,
            'totalIn'      => $totalIn,
            'totalOut'     => $totalOut,
            'printedBy'    => auth()->user()->name ?? 'Admin Utama',
            'printedAt'    => now()->translatedFormat('d F Y, H:i'),
        ])->setPaper('a4', 'landscape');

        $filename = "Laporan-Mutasi-Stok-{$startDate}-sd-{$endDate}.pdf";
        return $pdf->stream($filename);
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->query('end_date', now()->format('Y-m-d'));
        $transactions = $this->getFilteredQuery($request)->get();

        $filename = "Laporan-Mutasi-Stok-{$startDate}-sd-{$endDate}.csv";

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar Excel langsung membaca karakter & kolom dengan rapi
            fwrite($handle, "\xEF\xBB\xBF");

            // Header Kolom
            fputcsv($handle, [
                'No',
                'Kode Referensi',
                'Tanggal',
                'Jenis',
                'SKU',
                'Nama Barang',
                'Kategori',
                'Supplier',
                'Jumlah (Unit)',
                'Catatan',
            ]);

            foreach ($transactions as $index => $trx) {
                fputcsv($handle, [
                    $index + 1,
                    $trx->reference_code ?? '#TRX-' . $trx->id,
                    ($trx->transaction_date ?? $trx->created_at)->format('Y-m-d'),
                    $trx->type === 'in' ? 'Barang Masuk' : 'Barang Keluar',
                    $trx->product?->sku ?? '-',
                    $trx->product?->name ?? 'Barang Dihapus',
                    $trx->product?->category?->name ?? '-',
                    $trx->supplier?->name ?? '-',
                    ($trx->type === 'in' ? '+' : '-') . $trx->quantity,
                    $trx->notes ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}