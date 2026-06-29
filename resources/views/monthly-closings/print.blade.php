<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print {{ $closing->closing_number }}</title>
    <style>
        body{font-family:Arial,sans-serif;font-size:13px;color:#111}table{width:100%;border-collapse:collapse;margin-top:12px}th,td{border:1px solid #ddd;padding:8px}th{background:#f4f4f4;text-align:left}.right{text-align:right}.summary{margin-top:16px;width:60%;margin-left:auto}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}@media print{button{display:none}.grid{display:block}}
    </style>
</head>
<body>
<button onclick="window.print()">Print</button>
@php
    $shipGroups = $closing->items->groupBy(fn($item) => $item->ship_id ?: $item->ship_name);
    $operationalExpenses = $closing->expenses->where('expense_type', \App\Models\OwnerExpense::TYPE_OPERATIONAL);
    $nonOperationalExpenses = $closing->expenses->where('expense_type', \App\Models\OwnerExpense::TYPE_NON_OPERATIONAL);
@endphp
<h2>Grand Invoice Tutup Bulan</h2>
<p>
    <strong>Nomor:</strong> {{ $closing->closing_number }}<br>
    <strong>Periode:</strong> {{ $closing->month }}/{{ $closing->year }}<br>
    <strong>Total Kapal:</strong> {{ $closing->total_ships }}<br>
    <strong>Total Invoice:</strong> {{ $closing->total_invoices }}<br>
    <strong>Persentase Jasa Kapten:</strong> {{ $closing->captain_percentage }}%
</p>

<h3>Rekap per Kapal</h3>
<table><thead><tr><th>Kapal</th><th>Kapten</th><th class="right">Invoice</th><th class="right">Gabus</th><th class="right">Bersih Harian</th><th class="right">Operasional</th><th class="right">Dasar Bagi</th><th class="right">Jasa Kapten</th><th class="right">Owner</th></tr></thead><tbody>@foreach($shipGroups as $group)<tr><td>{{ $group->first()->ship_name ?? $group->first()->ship?->name }}</td><td>{{ $group->first()->captain_name ?? $group->first()->captain?->name }}</td><td class="right">{{ $group->count() }}</td><td class="right">{{ $group->sum('total_boxes') }}</td><td class="right">{{ \App\Support\Money::rupiah($group->sum('net_income')) }}</td><td class="right">{{ \App\Support\Money::rupiah($group->sum('operational_expense')) }}</td><td class="right">{{ \App\Support\Money::rupiah($group->sum('distributable_income')) }}</td><td class="right">{{ \App\Support\Money::rupiah($group->sum('captain_share')) }}</td><td class="right">{{ \App\Support\Money::rupiah($group->sum('owner_share')) }}</td></tr>@endforeach</tbody></table>

<div class="grid">
    <div>
        <h3>Biaya Operasional Bulanan</h3>
        <table><tbody>@forelse($operationalExpenses as $expense)<tr><td>{{ $expense->expense_date->format('d/m/Y') }}</td><td>{{ $expense->description }}</td><td class="right">{{ \App\Support\Money::rupiah($expense->amount) }}</td></tr>@empty<tr><td>Tidak ada biaya operasional.</td></tr>@endforelse</tbody></table>
    </div>
    <div>
        <h3>Pengeluaran Non-Operasional</h3>
        <table><tbody>@forelse($nonOperationalExpenses as $expense)<tr><td>{{ $expense->expense_date->format('d/m/Y') }}</td><td>{{ $expense->description }}</td><td class="right">{{ \App\Support\Money::rupiah($expense->amount) }}</td></tr>@empty<tr><td>Tidak ada pengeluaran non-operasional.</td></tr>@endforelse</tbody></table>
    </div>
</div>

<h3>Detail Invoice</h3>
<table><thead><tr><th>Invoice</th><th>Tanggal</th><th>Kapal</th><th>Kapten</th><th class="right">Gabus</th><th class="right">Pemasukan</th><th class="right">Ongkir+Gabus</th><th class="right">Bersih Harian</th><th class="right">Alokasi Op.</th><th class="right">Dasar Bagi</th></tr></thead><tbody>@foreach($closing->items as $item)<tr><td>{{ $item->invoice?->invoice_number }}</td><td>{{ $item->invoice_date->format('d/m/Y') }}</td><td>{{ $item->ship_name ?? $item->ship?->name }}</td><td>{{ $item->captain_name ?? $item->captain?->name }}</td><td class="right">{{ $item->total_boxes }}</td><td class="right">{{ \App\Support\Money::rupiah($item->total_income) }}</td><td class="right">{{ \App\Support\Money::rupiah($item->total_expense) }}</td><td class="right">{{ \App\Support\Money::rupiah($item->net_income) }}</td><td class="right">{{ \App\Support\Money::rupiah($item->operational_expense) }}</td><td class="right">{{ \App\Support\Money::rupiah($item->distributable_income) }}</td></tr>@endforeach</tbody></table>

<table class="summary">
    <tr><th>Total Pemasukan Kotor</th><td class="right">{{ \App\Support\Money::rupiah($closing->total_income) }}</td></tr>
    <tr><th>Ongkir + Jasa Angkat Gabus</th><td class="right">{{ \App\Support\Money::rupiah($closing->total_expense) }}</td></tr>
    <tr><th>Bersih Harian Invoice</th><td class="right">{{ \App\Support\Money::rupiah($closing->daily_net_income ?: $closing->net_income) }}</td></tr>
    <tr><th>Biaya Operasional Bulanan</th><td class="right">{{ \App\Support\Money::rupiah($closing->operational_expense_total) }}</td></tr>
    <tr><th>Dasar Pembagian</th><td class="right">{{ \App\Support\Money::rupiah($closing->distributable_income) }}</td></tr>
    <tr><th>Jasa Kapten</th><td class="right">{{ \App\Support\Money::rupiah($closing->captain_share) }}</td></tr>
    <tr><th>Bagian Owner</th><td class="right">{{ \App\Support\Money::rupiah($closing->owner_share) }}</td></tr>
    <tr><th>Pengeluaran Non-Operasional</th><td class="right">{{ \App\Support\Money::rupiah($closing->non_operational_expense_total) }}</td></tr>
    <tr><th>Owner Final Setelah Non-Operasional</th><td class="right"><strong>{{ \App\Support\Money::rupiah($closing->owner_final_income) }}</strong></td></tr>
</table>
</body>
</html>
