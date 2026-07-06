<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Screenshot {{ $closing->display_period }}</title>
    <style>
        *{box-sizing:border-box} body{margin:0;background:#ecfdf5;font-family:Arial,Helvetica,sans-serif;color:#0f172a} .wrap{max-width:430px;margin:0 auto;min-height:100vh;background:#f8faf7;padding:18px}.card{background:#fff;border:1px solid #dbe7e2;border-radius:24px;padding:16px;margin-bottom:12px;box-shadow:0 10px 30px rgba(7,59,58,.08)}.hero{background:linear-gradient(135deg,#073b3a,#0f766e);color:#fff;border-radius:28px;padding:20px;margin-bottom:14px}.muted{color:#64748b;font-size:12px}.hero .muted{color:#ccfbf1}.title{font-size:24px;font-weight:900;margin:0}.num{font-size:12px;margin-top:6px;color:#ccfbf1}.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.label{font-size:11px;color:#64748b}.value{font-weight:900;font-size:15px;margin-top:4px}.value.green{color:#047857}.value.amber{color:#b45309}.ship{border-top:1px dashed #cbd5e1;padding-top:12px;margin-top:12px}.ship-title{font-size:16px;font-weight:900}.row{display:flex;justify-content:space-between;gap:12px;margin-top:8px;font-size:13px}.row strong{white-space:nowrap}.big{font-size:20px}.foot{font-size:11px;color:#64748b;text-align:center;margin:14px 0}.actions{position:sticky;top:0;background:#f8faf7;padding:8px 0 12px;margin-bottom:6px}.btn{width:100%;border:0;border-radius:18px;background:#073b3a;color:#fff;font-weight:900;padding:13px} @media print{.actions{display:none}.wrap{max-width:none}.card,.hero{box-shadow:none}}
    </style>
</head>
<body>
<div class="wrap" id="screenshotArea">
    <div class="actions"><button class="btn" onclick="window.print()">Simpan dari Browser / Screenshot HP</button></div>
    <section class="hero">
        <div class="muted">Baleta · Rekap Tutup Bulan</div>
        <h1 class="title">{{ $closing->display_period }}</h1>
        <div class="num">{{ $closing->closing_number }} · Invoice {{ $closing->display_date_range }}</div>
    </section>
    <section class="card">
        <div class="grid">
            <div><div class="label">Bersih Harian</div><div class="value">{{ \App\Support\Money::rupiah($closing->daily_net_income) }}</div></div>
            <div><div class="label">Operasional</div><div class="value amber">{{ \App\Support\Money::rupiah($closing->operational_expense_total) }}</div></div>
            <div><div class="label">Jasa Kapten</div><div class="value">{{ \App\Support\Money::rupiah($closing->captain_share) }}</div></div>
            <div><div class="label">Hasil Pemilik dari Kapal</div><div class="value green big">{{ \App\Support\Money::rupiah($closing->owner_share) }}</div></div>
        </div>
    </section>
    <section class="card">
        <div class="ship-title">Rincian Kapal</div>
        @foreach($closing->shipItems as $item)
            <div class="ship">
                <div class="row"><span class="ship-title">{{ $item->ship_name }}</span><strong>{{ \App\Support\Money::rupiah($item->owner_share) }}</strong></div>
                <div class="muted">Kapten: {{ $item->captain_name }} · {{ $item->total_invoices }} invoice</div>
                <div class="row"><span>Bersih harian</span><strong>{{ \App\Support\Money::rupiah($item->total_daily_net_income) }}</strong></div>
                <div class="row"><span>Operasional</span><strong>{{ \App\Support\Money::rupiah($item->total_ship_operational_expense) }}</strong></div>
                <div class="row"><span>Jasa kapten {{ $item->captain_percentage }}%</span><strong>{{ \App\Support\Money::rupiah($item->captain_share) }}</strong></div>
            </div>
        @endforeach
    </section>
    @if($closing->notes)
        <section class="card"><div class="label">Catatan</div><div class="value">{{ $closing->notes }}</div></section>
    @endif
    <div class="foot">Baleta · Dibuat {{ $closing->closed_at?->format('d/m/Y H:i') ?: $closing->created_at?->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>
