<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Screenshot Invoice {{ $invoice->invoice_number }}</title>
    <style>
        *{box-sizing:border-box} body{margin:0;background:#ecfdf5;font-family:Arial,Helvetica,sans-serif;color:#0f172a}.wrap{max-width:430px;margin:0 auto;min-height:100vh;background:#f8faf7;padding:18px}.hero{background:linear-gradient(135deg,#073b3a,#0f766e);color:#fff;border-radius:28px;padding:20px;margin-bottom:14px}.card{background:#fff;border:1px solid #dbe7e2;border-radius:24px;padding:16px;margin-bottom:12px;box-shadow:0 10px 30px rgba(7,59,58,.08)}.muted{color:#64748b;font-size:12px}.hero .muted{color:#ccfbf1}.title{font-size:23px;font-weight:900;margin:0}.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.label{font-size:11px;color:#64748b}.value{font-weight:900;font-size:15px;margin-top:4px}.green{color:#047857}.amber{color:#b45309}.row{display:flex;justify-content:space-between;gap:12px;margin-top:9px;font-size:13px}.row strong{white-space:nowrap}.section-title{font-weight:900;margin-bottom:8px}.actions{position:sticky;top:0;background:#f8faf7;padding:8px 0 12px;margin-bottom:6px}.btn{width:100%;border:0;border-radius:18px;background:#073b3a;color:#fff;font-weight:900;padding:13px}.foot{font-size:11px;color:#64748b;text-align:center;margin:14px 0}@media print{.actions{display:none}.card,.hero{box-shadow:none}}
    </style>
</head>
<body>
@php $detailBoxes = (int) $invoice->items->sum('box_count'); @endphp
<div class="wrap">
    <div class="actions"><button class="btn" onclick="window.print()">Simpan dari Browser / Screenshot HP</button></div>
    <section class="hero">
        <div class="muted">Baleta · Invoice Harian</div>
        <h1 class="title">{{ $invoice->invoice_number }}</h1>
        <div class="muted">{{ $invoice->invoice_date->format('d/m/Y') }} · {{ $invoice->ship?->name }} · {{ $invoice->status_label }}</div>
    </section>
    <section class="card">
        <div class="grid">
            <div><div class="label">Kapal</div><div class="value">{{ $invoice->ship?->name }}</div></div>
            <div><div class="label">Kapten</div><div class="value">{{ $invoice->captain?->name }}</div></div>
            <div><div class="label">Gabus Turun</div><div class="value">{{ $invoice->total_boxes }}</div></div>
            <div><div class="label">Gabus Dibeli</div><div class="value">{{ $detailBoxes }}</div></div>
            <div><div class="label">Pemasukan</div><div class="value">{{ \App\Support\Money::rupiah($invoice->total_income) }}</div></div>
            <div><div class="label">Pengeluaran</div><div class="value amber">{{ \App\Support\Money::rupiah($invoice->total_expense) }}</div></div>
            <div style="grid-column:1 / -1"><div class="label">Bersih Harian</div><div class="value green" style="font-size:22px">{{ \App\Support\Money::rupiah($invoice->net_income) }}</div></div>
        </div>
    </section>
    <section class="card">
        <div class="section-title">Pembeli</div>
        @foreach($invoice->items as $item)
            <div class="row"><span>{{ $item->display_buyer_name }}<br><span class="muted">{{ $item->box_count }} gabus × {{ \App\Support\Money::rupiah($item->price_per_box) }}</span></span><strong>{{ \App\Support\Money::rupiah($item->subtotal) }}</strong></div>
        @endforeach
    </section>
    <section class="card">
        <div class="section-title">Pengeluaran Harian</div>
        @foreach($invoice->expenses as $expense)
            <div class="row"><span>{{ $expense->description }}<br><span class="muted">Qty {{ $expense->quantity }}</span></span><strong>{{ \App\Support\Money::rupiah($expense->amount) }}</strong></div>
        @endforeach
    </section>
    @if($invoice->notes)
        <section class="card"><div class="label">Catatan</div><div class="value">{{ $invoice->notes }}</div></section>
    @endif
    <div class="foot">Baleta · Dibuat {{ now()->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>
