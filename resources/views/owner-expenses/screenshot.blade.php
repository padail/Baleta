<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Screenshot Non-Operasional</title>
    <style>
        *{box-sizing:border-box} body{margin:0;background:#fff7ed;font-family:Arial,Helvetica,sans-serif;color:#0f172a}.wrap{max-width:430px;margin:0 auto;min-height:100vh;background:#fffaf3;padding:18px}.hero{background:linear-gradient(135deg,#073b3a,#0f766e);color:#fff;border-radius:28px;padding:20px;margin-bottom:14px}.card{background:#fff;border:1px solid #fed7aa;border-radius:24px;padding:16px;margin-bottom:12px;box-shadow:0 10px 30px rgba(124,45,18,.08)}.muted{color:#64748b;font-size:12px}.hero .muted{color:#ccfbf1}.title{font-size:23px;font-weight:900;margin:0}.row{display:flex;justify-content:space-between;gap:12px;margin-top:10px;font-size:13px}.row strong{white-space:nowrap}.total{font-size:23px;font-weight:900;color:#b45309}.actions{position:sticky;top:0;background:#fffaf3;padding:8px 0 12px;margin-bottom:6px}.btn{width:100%;border:0;border-radius:18px;background:#073b3a;color:#fff;font-weight:900;padding:13px}.foot{font-size:11px;color:#64748b;text-align:center;margin:14px 0}@media print{.actions{display:none}.card,.hero{box-shadow:none}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="actions"><button class="btn" onclick="window.print()">Simpan dari Browser / Screenshot HP</button></div>
    <section class="hero"><div class="muted">Baleta · Rekap Terpisah</div><h1 class="title">Non-Operasional</h1><div class="muted">Tidak masuk tutup bulan kapal</div></section>
    <section class="card"><div class="muted">Total Pengeluaran</div><div class="total">{{ \App\Support\Money::rupiah($total) }}</div></section>
    <section class="card">
        @forelse($expenses as $expense)
            <div class="row"><span>{{ $expense->description }}<br><span class="muted">{{ $expense->expense_date->format('d/m/Y') }}</span></span><strong>{{ \App\Support\Money::rupiah($expense->amount) }}</strong></div>
        @empty
            <div class="muted">Belum ada data.</div>
        @endforelse
    </section>
    <div class="foot">Baleta · Dibuat {{ now()->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>
