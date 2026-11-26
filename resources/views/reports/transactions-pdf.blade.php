<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Mutasi Stok — Inventura</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 10px;
        }

        .header {
            border-bottom: 3px solid #0d9488;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
            color: #0f766e;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        .report-title {
            text-align: right;
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
        }

        .report-meta {
            text-align: right;
            font-size: 10px;
            color: #475569;
            margin-top: 3px;
        }

        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 16px -8px;
        }

        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            width: 25%;
        }

        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 4px;
            color: #0f172a;
        }

        .text-in {
            color: #059669;
        }

        .text-out {
            color: #e11d48;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.data-table th {
            background-color: #0f766e;
            color: #ffffff;
            text-align: left;
            padding: 8px;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #0f766e;
        }

        table.data-table td {
            padding: 7px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-in {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-out {
            background-color: #ffe4e6;
            color: #9f1239;
        }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        .footer-table {
            width: 100%;
        }
    </style>
</head>

<body>

    <!-- Kop Laporan -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand">Inventura.</div>
                    <div class="subtitle">Sistem Manajemen Inventaris &amp; Aset Gudang</div>
                </td>
                <td>
                    <div class="report-title">LAPORAN MUTASI STOK BARANG</div>
                    <div class="report-meta">
                        Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> s/d
                        <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
                        &bull; Jenis:
                        <strong>{{ $type === 'in' ? 'Barang Masuk' : ($type === 'out' ? 'Barang Keluar' : 'Semua Mutasi') }}</strong>
                        &bull; Barang: <strong>{{ $productName }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Ringkasan Periode -->
    <table class="summary-table">
        <tr>
            <td class="summary-card">
                <div class="summary-label">Total Transaksi</div>
                <div class="summary-value">{{ number_format($transactions->count()) }} Aktivitas</div>
            </td>
            <td class="summary-card">
                <div class="summary-label">Total Barang Masuk</div>
                <div class="summary-value text-in">+{{ number_format($totalIn) }} Unit</div>
            </td>
            <td class="summary-card">
                <div class="summary-label">Total Barang Keluar</div>
                <div class="summary-value text-out">-{{ number_format($totalOut) }} Unit</div>
            </td>
            <td class="summary-card">
                <div class="summary-label">Selisih Bersih (Net)</div>
                @php $net = $totalIn - $totalOut; @endphp
                <div class="summary-value {{ $net >= 0 ? 'text-in' : 'text-out' }}">
                    {{ $net >= 0 ? '+' : '' }}{{ number_format($net) }} Unit
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">No</th>
                <th style="width: 15%;">Kode Referensi</th>
                <th class="text-center" style="width: 10%;">Tanggal</th>
                <th class="text-center" style="width: 9%;">Jenis</th>
                <th style="width: 22%;">Barang (SKU)</th>
                <th style="width: 15%;">Supplier</th>
                <th class="text-right" style="width: 10%;">Jumlah</th>
                <th style="width: 15%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $index => $trx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace;">{{ $trx->reference_code ?? '#TRX-' . $trx->id }}</td>
                    <td class="text-center">{{ ($trx->transaction_date ?? $trx->created_at)->format('d/m/Y') }}</td>
                    <td class="text-center">
                        @if ($trx->type === 'in')
                            <span class="badge badge-in">MASUK</span>
                        @else
                            <span class="badge badge-out">KELUAR</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $trx->product?->name ?? 'Barang Dihapus' }}</strong><br>
                        <span style="color: #64748b; font-size: 9px;">{{ $trx->product?->sku ?? '-' }} &bull;
                            {{ $trx->product?->category?->name ?? '-' }}</span>
                    </td>
                    <td>{{ $trx->supplier?->name ?? '-' }}</td>
                    <td class="text-right {{ $trx->type === 'in' ? 'text-in' : 'text-out' }}"
                        style="font-weight: bold;">
                        {{ $trx->type === 'in' ? '+' : '-' }}{{ number_format($trx->quantity) }} Unit
                    </td>
                    <td>{{ $trx->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada data transaksi pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>Dicetak oleh: <strong>{{ $printedBy }}</strong></td>
                <td class="text-right">Tanggal cetak: {{ $printedAt }} WIB</td>
            </tr>
        </table>
    </div>

</body>

</html>
