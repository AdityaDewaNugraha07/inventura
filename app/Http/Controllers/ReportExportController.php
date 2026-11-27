<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportExportController extends Controller
{
    private function getFilteredQuery(Request $request)
    {
        $nowWib    = Carbon::now('Asia/Jakarta');
        $startDate = $request->query('start_date', $nowWib->copy()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->query('end_date', $nowWib->format('Y-m-d'));
        $type      = $request->query('type', '');
        $productId = $request->query('product_id', '');

        return Transaction::with(['product.category', 'supplier'])
            ->whereRaw('DATE(COALESCE(transaction_date, created_at)) >= ?', [$startDate])
            ->whereRaw('DATE(COALESCE(transaction_date, created_at)) <= ?', [$endDate])
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->when($productId !== '', fn ($q) => $q->where('product_id', $productId))
            ->orderByRaw('COALESCE(transaction_date, created_at) DESC')
            ->latest('id');
    }

    public function exportPdf(Request $request)
    {
        $nowWib    = Carbon::now('Asia/Jakarta');
        $startDate = $request->query('start_date', $nowWib->copy()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->query('end_date', $nowWib->format('Y-m-d'));
        $type      = $request->query('type', '');
        $productId = $request->query('product_id', '');

        $transactions = $this->getFilteredQuery($request)->get();
        $totalIn      = $transactions->where('type', 'in')->sum('quantity');
        $totalOut     = $transactions->where('type', 'out')->sum('quantity');
        $productName  = $productId ? (Product::find($productId)?->name ?? 'Semua Barang') : 'Semua Barang';

        // Kunci direktori temp & font di dalam storage agar tidak diblokir open_basedir InfinityFree
        $dompdfTemp = storage_path('app/dompdf');
        $dompdfFont = storage_path('fonts');

        if (! is_dir($dompdfTemp)) {
            mkdir($dompdfTemp, 0755, true);
        }
        if (! is_dir($dompdfFont)) {
            mkdir($dompdfFont, 0755, true);
        }

        config(['dompdf.public_path' => public_path()]);
        
        $pdf = Pdf::setOption('tempDir', $dompdfTemp)
            ->setOption('fontDir', $dompdfFont)
            ->setOption('fontCache', $dompdfFont)
            ->setOption('chroot', [base_path(), public_path()])
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->loadView('reports.transactions-pdf', [
                'transactions' => $transactions,
                'startDate'    => $startDate,
                'endDate'      => $endDate,
                'type'         => $type,
                'productName'  => $productName,
                'totalIn'      => $totalIn,
                'totalOut'     => $totalOut,
                'printedBy'    => auth()->user()?->name ?? 'Admin Utama',
                'printedAt'    => $nowWib->locale('id')->translatedFormat('d F Y, H:i') . ' WIB',
            ])
            ->setPaper('a4', 'landscape');

        $filename = "Laporan-Mutasi-Stok-{$startDate}-sd-{$endDate}.pdf";

        return $pdf->stream($filename);
    }

    public function exportExcel(Request $request)
    {
        $nowWib    = Carbon::now('Asia/Jakarta');
        $startDate = $request->query('start_date', $nowWib->copy()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->query('end_date', $nowWib->format('Y-m-d'));
        $type      = $request->query('type', '');
        $productId = $request->query('product_id', '');

        $transactions = $this->getFilteredQuery($request)->get();
        $totalTrx     = $transactions->count();
        $totalIn      = (int) $transactions->where('type', 'in')->sum('quantity');
        $totalOut     = (int) $transactions->where('type', 'out')->sum('quantity');
        $netChange    = $totalIn - $totalOut;
        $productName  = $productId ? (Product::find($productId)?->name ?? 'Semua Barang') : 'Semua Barang';

        $typeLabel = match ($type) {
            'in'    => 'Barang Masuk',
            'out'   => 'Barang Keluar',
            default => 'Semua Jenis',
        };

        $periodLabel = Carbon::parse($startDate)->locale('id')->translatedFormat('d M Y')
            . ' s/d '
            . Carbon::parse($endDate)->locale('id')->translatedFormat('d M Y');
        $printedBy = auth()->user()?->name ?? 'Admin Utama';
        $printedAt = $nowWib->locale('id')->translatedFormat('d F Y, H:i') . ' WIB';

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Mutasi Stok');
        $sheet->setShowGridlines(false);

        // Font default seluruh sheet
        $spreadsheet->getDefaultStyle()->getFont()
            ->setName('Calibri')
            ->setSize(10)
            ->getColor()->setRGB('1E293B');

        // ============================================================
        // 1. KOP LAPORAN (DEEP TEAL BANNER ALA CETAK PDF INVENTURA)
        // ============================================================
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'INVENTURA.  |  LAPORAN MUTASI STOK INVENTARIS');
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 15,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F3D3E'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'indent'     => 1,
            ],
        ]);

        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue(
            'A2',
            "Periode: {$periodLabel}   •   Jenis: {$typeLabel}   •   Barang: {$productName}   •   Dicetak: {$printedAt} oleh {$printedBy}"
        );
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getStyle('A2:J2')->applyFromArray([
            'font' => [
                'size'  => 9.5,
                'color' => ['rgb' => '99F6E4'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '134E4A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'indent'     => 1,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => '14B8A6'],
                ],
            ],
        ]);

        // Spasi kosong
        $sheet->getRowDimension(3)->setRowHeight(10);

        // ============================================================
        // 2. KARTU RINGKASAN KPI (4 BLOK SEJAJAR A:J)
        // ============================================================
        $kpiCards = [
            [
                'start'  => 'A',
                'end'    => 'C',
                'label'  => 'TOTAL AKTIVITAS',
                'value'  => number_format($totalTrx, 0, ',', '.') . ' Transaksi',
                'bg'     => 'F0FDFA',
                'border' => '99F6E4',
                'color'  => '0F766E',
            ],
            [
                'start'  => 'D',
                'end'    => 'F',
                'label'  => 'TOTAL BARANG MASUK',
                'value'  => '+' . number_format($totalIn, 0, ',', '.') . ' Unit',
                'bg'     => 'ECFDF5',
                'border' => 'A7F3D0',
                'color'  => '047857',
            ],
            [
                'start'  => 'G',
                'end'    => 'H',
                'label'  => 'TOTAL BARANG KELUAR',
                'value'  => '-' . number_format($totalOut, 0, ',', '.') . ' Unit',
                'bg'     => 'FFF1F2',
                'border' => 'FECDD3',
                'color'  => 'BE123C',
            ],
            [
                'start'  => 'I',
                'end'    => 'J',
                'label'  => 'SELISIH STOK (NET)',
                'value'  => ($netChange >= 0 ? '+' : '') . number_format($netChange, 0, ',', '.') . ' Unit',
                'bg'     => 'F8FAFC',
                'border' => 'CBD5E1',
                'color'  => $netChange >= 0 ? '0F766E' : 'BE123C',
            ],
        ];

        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension(5)->setRowHeight(24);

        foreach ($kpiCards as $card) {
            $s = $card['start'];
            $e = $card['end'];

            $sheet->mergeCells("{$s}4:{$e}4");
            $sheet->mergeCells("{$s}5:{$e}5");
            $sheet->setCellValue("{$s}4", $card['label']);
            $sheet->setCellValue("{$s}5", $card['value']);

            $sheet->getStyle("{$s}4:{$e}4")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 8.5, 'color' => ['rgb' => '64748B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM],
            ]);

            $sheet->getStyle("{$s}5:{$e}5")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => $card['color']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_TOP],
            ]);

            $sheet->getStyle("{$s}4:{$e}5")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $card['bg']]],
                'borders' => [
                    'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $card['border']]],
                ],
            ]);
        }

        // Spasi kosong sebelum header tabel
        $sheet->getRowDimension(6)->setRowHeight(12);

        // ============================================================
        // 3. HEADER TABEL UTAMA (BARIS 7)
        // ============================================================
        $headerRow = 7;
        $headers   = [
            'A' => 'NO',
            'B' => 'KODE REFERENSI',
            'C' => 'TANGGAL',
            'D' => 'JENIS MUTASI',
            'E' => 'SKU',
            'F' => 'NAMA BARANG',
            'G' => 'KATEGORI',
            'H' => 'SUPPLIER',
            'I' => 'JUMLAH (UNIT)',
            'J' => 'CATATAN',
        ];

        $sheet->getRowDimension($headerRow)->setRowHeight(26);
        foreach ($headers as $col => $title) {
            $sheet->setCellValue("{$col}{$headerRow}", $title);
        }

        $sheet->getStyle("A{$headerRow}:J{$headerRow}")->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 9.5,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0D9488'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '0F766E'],
                ],
            ],
        ]);

        // ============================================================
        // 4. ISI DATA TRANSAKSI (MULAI BARIS 8)
        // ============================================================
        $startDataRow = 8;
        $currentRow   = $startDataRow;

        if ($transactions->isEmpty()) {
            $sheet->mergeCells("A{$currentRow}:J{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'Tidak ada data mutasi stok pada periode dan filter yang dipilih.');
            $sheet->getRowDimension($currentRow)->setRowHeight(30);
            $sheet->getStyle("A{$currentRow}:J{$currentRow}")->applyFromArray([
                'font'      => ['italic' => true, 'color' => ['rgb' => '64748B']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders'   => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                ],
            ]);
            $currentRow++;
        } else {
            foreach ($transactions->values() as $index => $trx) {
                $isIn      = $trx->type === 'in';
                $signedQty = $isIn ? (int) $trx->quantity : -((int) $trx->quantity);
                $dateStr   = Carbon::parse($trx->transaction_date ?? $trx->created_at)
                    ->timezone('Asia/Jakarta')
                    ->format('d/m/Y');

                $sheet->setCellValue("A{$currentRow}", $index + 1);
                $sheet->setCellValue("B{$currentRow}", $trx->reference_code ?? '#TRX-' . $trx->id);
                $sheet->setCellValue("C{$currentRow}", $dateStr);
                $sheet->setCellValue("D{$currentRow}", $isIn ? 'BARANG MASUK' : 'BARANG KELUAR');
                $sheet->setCellValue("E{$currentRow}", $trx->product?->sku ?? '-');
                $sheet->setCellValue("F{$currentRow}", $trx->product?->name ?? 'Barang Dihapus');
                $sheet->setCellValue("G{$currentRow}", $trx->product?->category?->name ?? '-');
                $sheet->setCellValue("H{$currentRow}", $trx->supplier?->name ?? '-');
                $sheet->setCellValue("I{$currentRow}", $signedQty);
                $sheet->setCellValue("J{$currentRow}", $trx->notes ?: '-');

                $sheet->getRowDimension($currentRow)->setRowHeight(22);

                // Zebra striping halus
                $rowBg = $index % 2 === 1 ? 'F8FAFC' : 'FFFFFF';
                $sheet->getStyle("A{$currentRow}:J{$currentRow}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $rowBg],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'E2E8F0'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Perataan tiap kolom
                $sheet->getStyle("A{$currentRow}:E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("J{$currentRow}")->getAlignment()->setWrapText(true);

                // Aksen warna SKU
                $sheet->getStyle("E{$currentRow}")->getFont()->setBold(true)->getColor()->setRGB('0F766E');

                // Warna badge Jenis Mutasi & Angka Jumlah (+ Hijau / - Merah)
                $badgeBg   = $isIn ? 'D1FAE5' : 'FFE4E6';
                $badgeText = $isIn ? '065F46' : '9F1239';

                $sheet->getStyle("D{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $badgeText]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $badgeBg]],
                ]);

                $sheet->getStyle("I{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $badgeText]],
                ]);
                $sheet->getStyle("I{$currentRow}")->getNumberFormat()->setFormatCode('+#,##0;-#,##0;0');

                $currentRow++;
            }

            // ============================================================
            // 5. BARIS TOTAL DENGAN FORMULA EXCEL ASLI (=SUM)
            // ============================================================
            $lastDataRow = $currentRow - 1;
            $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'TOTAL PERGERAKAN BERSIH (NET MUTASI)');
            $sheet->setCellValue("I{$currentRow}", "=SUM(I{$startDataRow}:I{$lastDataRow})");
            $sheet->setCellValue(
                "J{$currentRow}",
                'Masuk: +' . number_format($totalIn, 0, ',', '.') . ' | Keluar: -' . number_format($totalOut, 0, ',', '.')
            );

            $sheet->getRowDimension($currentRow)->setRowHeight(26);
            $sheet->getStyle("A{$currentRow}:J{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F3D3E']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CCFBF1']],
                'borders' => [
                    'top'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0D9488']],
                    'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '0F3D3E']],
                    'left'   => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '99F6E4']],
                    'right'  => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '99F6E4']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$currentRow}")->getNumberFormat()->setFormatCode('+#,##0;-#,##0;0');
            $sheet->getStyle("J{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // ============================================================
        // 6. LEBAR KOLOM, FREEZE PANE, AUTO-FILTER & SIAP CETAK
        // ============================================================
        $columnWidths = [
            'A' => 6,
            'B' => 18,
            'C' => 14,
            'D' => 17,
            'E' => 14,
            'F' => 26,
            'G' => 18,
            'H' => 22,
            'I' => 16,
            'J' => 30,
        ];
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->freezePane('A8');
        $sheet->setAutoFilter("A{$headerRow}:J{$headerRow}");

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.5)->setRight(0.35)->setLeft(0.35)->setBottom(0.5);

        // Simpan sementara di storage/app/exports agar 100% aman dari batas open_basedir /tmp di InfinityFree
        $exportDir = storage_path('app/exports');
        if (! is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $tempPath = $exportDir . '/' . uniqid('inventura_xlsx_', true) . '.xlsx';
        $writer   = new Xlsx($spreadsheet);
        $writer->save($tempPath);
        $spreadsheet->disconnectWorksheets();

        $filename = "Laporan-Mutasi-Stok-{$startDate}-sd-{$endDate}.xlsx";

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}