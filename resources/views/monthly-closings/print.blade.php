<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tutup Bulan {{ $closing->closing_number }}</title>
    <style>
        body{font-family:Arial,sans-serif;color:#111827;font-size:12px} .wrap{max-width:960px;margin:0 auto} h1,h2,h3{margin:0 0 6px} table{width:100%;border-collapse:collapse;margin:10px 0 18px} th,td{border:1px solid #d1d5db;padding:7px;text-align:left} th{background:#f3f4f6} .right{text-align:right}.summary{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:14px 0}.box{border:1px solid #d1d5db;padding:10px}.muted{color:#6b7280}.note{background:#fffbeb;border:1px solid #fde68a;padding:10px;margin:10px 0}.page-break{page-break-inside:avoid}@media print{button{display:none}}
    </style>
</head>
<body>
<div class="wrap">
    <button onclick="window.print()">Cetak</button>
    <h1>Rekap Final Owner dari Kapal</h1>
    <p class="muted">{{ $closing->closing_number }} · Periode {{ str_pad($closing->month, 2, '0', STR_PAD_LEFT) }}/{{ $closing->year }}</p>
    <div class="note">Pengeluaran non-operasional tidak termasuk dalam laporan ini dan memiliki rekap terpisah.</div>

    <div class="summary">
        <div class="box"><div class="muted">Bersih Harian</div><strong>{{ \App\Support\Money::rupiah($closing->daily_net_income) }}</strong></div>
        <div class="box"><div class="muted">Operasional Kapal</div><strong>{{ \App\Support\Money::rupiah($closing->operational_expense_total) }}</strong></div>
        <div class="box"><div class="muted">Jasa Kapten</div><strong>{{ \App\Support\Money::rupiah($closing->captain_share) }}</strong></div>
        <div class="box"><div class="muted">Owner Final</div><strong>{{ \App\Support\Money::rupiah($closing->owner_share) }}</strong></div>
    </div>

    <h2>Rekap Per Kapal</h2>
    <table>
        <thead><tr><th>Kapal</th><th>Kapten</th><th class="right">Invoice</th><th class="right">Bersih Harian</th><th class="right">Operasional</th><th class="right">Setelah Op.</th><th class="right">Jasa Kapten</th><th class="right">Owner</th></tr></thead>
        <tbody>
        @foreach($closing->shipItems as $item)
            <tr>
                <td>{{ $item->ship_name }}</td><td>{{ $item->captain_name }}</td><td class="right">{{ $item->total_invoices }}</td><td class="right">{{ \App\Support\Money::rupiah($item->total_daily_net_income) }}</td><td class="right">{{ \App\Support\Money::rupiah($item->total_ship_operational_expense) }}</td><td class="right">{{ \App\Support\Money::rupiah($item->net_after_ship_operational) }}</td><td class="right">{{ \App\Support\Money::rupiah($item->captain_share) }} ({{ $item->captain_percentage }}%)</td><td class="right">{{ \App\Support\Money::rupiah($item->owner_share) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @foreach($closing->shipItems as $item)
        <div class="page-break">
            <h3>{{ $item->ship_name }}</h3>
            <table>
                <thead><tr><th>Invoice</th><th>Tanggal</th><th class="right">Gabus</th><th class="right">Pemasukan</th><th class="right">Pengeluaran Harian</th><th class="right">Bersih Harian</th></tr></thead>
                <tbody>
                @foreach($item->invoiceItems as $invoice)
                    <tr><td>{{ $invoice->invoice_number }}</td><td>{{ $invoice->invoice_date->format('d/m/Y') }}</td><td class="right">{{ $invoice->total_boxes }}</td><td class="right">{{ \App\Support\Money::rupiah($invoice->total_income) }}</td><td class="right">{{ \App\Support\Money::rupiah($invoice->total_expense) }}</td><td class="right">{{ \App\Support\Money::rupiah($invoice->net_income) }}</td></tr>
                @endforeach
                </tbody>
            </table>
            <table>
                <thead><tr><th>Biaya Operasional Kapal</th><th class="right">Nominal</th></tr></thead>
                <tbody>
                @forelse($item->operationalExpenses as $expense)
                    <tr><td>{{ $expense->description }}</td><td class="right">{{ \App\Support\Money::rupiah($expense->amount) }}</td></tr>
                @empty
                    <tr><td colspan="2">Tidak ada biaya operasional kapal.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    <h2>Ringkasan Akhir</h2>
    <table>
        <tbody>
            <tr><td>Total Bersih Harian Semua Kapal</td><td class="right">{{ \App\Support\Money::rupiah($closing->daily_net_income) }}</td></tr>
            <tr><td>Total Operasional Kapal</td><td class="right">{{ \App\Support\Money::rupiah($closing->operational_expense_total) }}</td></tr>
            <tr><td>Total Jasa Kapten</td><td class="right">{{ \App\Support\Money::rupiah($closing->captain_share) }}</td></tr>
            <tr><th>Pendapatan Final Owner dari Kapal</th><th class="right">{{ \App\Support\Money::rupiah($closing->owner_share) }}</th></tr>
        </tbody>
    </table>
</div>
</body>
</html>
