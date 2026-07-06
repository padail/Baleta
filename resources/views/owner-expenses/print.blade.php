<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Rekap Non-Operasional</title>
    <style>
        body{font-family:Arial,sans-serif;color:#111827;font-size:12px}.wrap{max-width:900px;margin:0 auto}table{width:100%;border-collapse:collapse;margin-top:14px}th,td{border:1px solid #d1d5db;padding:8px;text-align:left}th{background:#f3f4f6}.right{text-align:right}.muted{color:#6b7280}.box{border:1px solid #d1d5db;padding:12px;margin:12px 0;background:#f9fafb}@media print{button{display:none}}
    </style>
</head>
<body>
<div class="wrap">
    <button onclick="window.print()">Cetak</button>
    <h1>Rekap Pengeluaran Non-Operasional</h1>
    <p class="muted">Laporan ini berdiri sendiri dan tidak masuk ke tutup bulan kapal.</p>
    <div class="box"><div class="muted">Total</div><strong>{{ \App\Support\Money::rupiah($total) }}</strong></div>
    <table>
        <thead><tr><th>Tanggal</th><th>Keterangan</th><th>Catatan</th><th class="right">Nominal</th></tr></thead>
        <tbody>
        @forelse($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                <td>{{ $expense->description }}</td>
                <td>{{ $expense->notes }}</td>
                <td class="right">{{ \App\Support\Money::rupiah($expense->amount) }}</td>
            </tr>
        @empty
            <tr><td colspan="4">Belum ada data.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr><th colspan="3">Total</th><th class="right">{{ \App\Support\Money::rupiah($total) }}</th></tr></tfoot>
    </table>
</div>
</body>
</html>
