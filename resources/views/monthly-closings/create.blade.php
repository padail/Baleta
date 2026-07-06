@extends('layouts.app')
@section('title', 'Buat Tutup Bulan')
@section('content')
<div class="mb-5"><a href="{{ route('monthly-closings.index') }}" class="inline-flex text-sm font-semibold text-slate-500 mb-3">← Daftar tutup bulan</a><h1 class="text-2xl md:text-3xl font-black">Buat Tutup Bulan</h1><p class="text-sm text-slate-500 mt-1">Invoice harian direkap per kapal, lalu menjadi final owner.</p></div>

<form method="GET" action="{{ route('monthly-closings.create') }}" data-allow-multi-submit="true" class="rounded-[1.75rem] bg-white p-4 md:p-5 shadow-sm border border-slate-100 mb-5 grid grid-cols-2 md:grid-cols-4 gap-3">
    <div><label class="block text-xs font-bold text-slate-500 mb-2">Bulan</label><input type="number" name="month" min="1" max="12" value="{{ request('month', now()->month) }}" required class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base" placeholder="Bulan"></div>
    <div><label class="block text-xs font-bold text-slate-500 mb-2">Tahun</label><input type="number" name="year" value="{{ request('year', now()->year) }}" required class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base" placeholder="Tahun"></div>
    <div class="col-span-2 flex items-end"><button class="w-full min-h-[52px] rounded-2xl bg-[#073b3a] text-white px-4 font-bold">Preview Rekap Kapal</button></div>
</form>

@if($preview)
<form method="POST" action="{{ route('monthly-closings.store') }}" class="space-y-5" id="closingForm">
@csrf
<input type="hidden" name="month" value="{{ request('month') }}"><input type="hidden" name="year" value="{{ request('year') }}">

<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100"><div class="text-xs text-slate-500">Kapal Direkap</div><div class="text-2xl font-black">{{ $preview['total_ships'] }}</div></div>
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100"><div class="text-xs text-slate-500">Invoice Harian</div><div class="text-2xl font-black">{{ $preview['total_invoices'] }}</div></div>
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100"><div class="text-xs text-slate-500">Gabus Tercatat</div><div class="text-2xl font-black">{{ $preview['total_boxes'] }}</div></div>
    <div class="rounded-[1.5rem] bg-teal-600 text-white p-4 shadow-sm"><div class="text-xs text-teal-100">Bersih Harian</div><div class="text-2xl font-black">{{ \App\Support\Money::rupiah($preview['daily_net_income']) }}</div></div>
</div>

<div class="rounded-[1.5rem] border border-teal-100 bg-teal-50 p-4 text-sm text-teal-950"><div class="font-black">Alur perhitungan</div><p class="mt-1">Setiap kapal dihitung sendiri. Bersih harian kapal dikurangi operasional bulanan kapal, lalu jasa kapten dihitung per kapal. Non-operasional tidak masuk halaman ini.</p></div>

<div class="space-y-4" id="shipCards">
@forelse($preview['ship_summaries'] as $ship)
    @php
        $oldShip = old('ships.'.$ship['ship_id'], []);
        $oldPercentage = $oldShip['captain_percentage'] ?? 20;
        $oldExpenses = $oldShip['operational_expenses'] ?? [
            ['description' => 'Kebutuhan pokok nelayan', 'amount' => 0],
            ['description' => 'Solar / BBM', 'amount' => 0],
            ['description' => 'Es / air bersih', 'amount' => 0],
        ];
    @endphp
    <section class="ship-card rounded-[1.75rem] bg-white shadow-sm border border-slate-100 overflow-hidden" data-ship-id="{{ $ship['ship_id'] }}" data-daily-net="{{ (int) $ship['total_daily_net_income'] }}">
        <div class="p-4 md:p-5 bg-[#073b3a] text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2"><div><h2 class="text-xl font-black">{{ $ship['ship_name'] }}</h2><p class="text-sm text-slate-300">Kapten: {{ $ship['captain_name'] }} · {{ $ship['total_invoices'] }} invoice harian</p></div><div class="md:text-right"><div class="text-xs text-slate-400">Bersih Harian Kapal</div><div class="text-2xl font-black text-emerald-300">{{ \App\Support\Money::rupiah($ship['total_daily_net_income']) }}</div></div></div>
        </div>
        <div class="p-4 md:p-5 grid lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2 rounded-2xl border border-slate-100 overflow-hidden"><div class="px-4 py-3 bg-slate-50 font-black text-sm">Invoice Harian</div><div class="divide-y divide-slate-100">@foreach($ship['invoices'] as $invoice)<div class="p-4 flex justify-between gap-3 text-sm"><div><div class="font-bold">{{ $invoice->invoice_number }}</div><div class="text-xs text-slate-500">{{ $invoice->invoice_date->format('d/m/Y') }} · {{ $invoice->total_boxes }} gabus</div></div><div class="font-black whitespace-nowrap">{{ \App\Support\Money::rupiah($invoice->net_income) }}</div></div>@endforeach</div></div>
            <div class="lg:col-span-3 space-y-4">
                <div class="rounded-2xl border border-slate-100 p-4"><div class="flex items-start justify-between gap-3 mb-3"><div><h3 class="font-black">Biaya Operasional Kapal</h3><p class="text-xs text-slate-500">Kebutuhan nelayan, solar, air, es, dan bekal melaut.</p></div><button type="button" class="add-expense rounded-2xl bg-[#073b3a] text-white px-4 py-3 text-xs font-bold" data-ship-id="{{ $ship['ship_id'] }}">Tambah</button></div><div class="space-y-2 operational-list" id="operational-list-{{ $ship['ship_id'] }}">@foreach($oldExpenses as $index => $expense)<div class="grid grid-cols-12 gap-2 operational-row"><input name="ships[{{ $ship['ship_id'] }}][operational_expenses][{{ $index }}][description]" value="{{ $expense['description'] ?? '' }}" class="col-span-7 min-h-[48px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base" placeholder="Keterangan"><input type="number" min="0" name="ships[{{ $ship['ship_id'] }}][operational_expenses][{{ $index }}][amount]" value="{{ $expense['amount'] ?? 0 }}" class="col-span-5 min-h-[48px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base operational-amount" placeholder="Nominal"></div>@endforeach</div></div>
                <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100"><div class="flex items-center justify-between mb-3"><label class="text-sm font-black">Jasa Kapten</label><div class="rounded-full bg-teal-100 text-teal-700 px-3 py-1 font-black"><span class="captain-percent-label">{{ $oldPercentage }}</span>%</div></div><input type="range" min="0" max="100" step="1" value="{{ $oldPercentage }}" class="captain-range w-full accent-teal-600" name="ships[{{ $ship['ship_id'] }}][captain_percentage]"><div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-4 text-sm"><div class="bg-white rounded-2xl p-3"><div class="text-xs text-slate-500">Operasional</div><div class="font-black operational-total-label">Rp0</div></div><div class="bg-white rounded-2xl p-3"><div class="text-xs text-slate-500">Setelah Op.</div><div class="font-black after-operational-label">Rp0</div></div><div class="bg-white rounded-2xl p-3"><div class="text-xs text-slate-500">Kapten</div><div class="font-black captain-share-label">Rp0</div></div><div class="bg-white rounded-2xl p-3"><div class="text-xs text-slate-500">Owner Kapal</div><div class="font-black text-green-700 owner-share-label">Rp0</div></div></div></div>
            </div>
        </div>
    </section>
@empty
    <div class="rounded-[1.5rem] bg-white p-8 text-center text-slate-500">Tidak ada invoice posted untuk periode ini.</div>
@endforelse
</div>

<div class="sticky bottom-24 md:bottom-4 z-20 rounded-[1.75rem] bg-[#073b3a] text-white shadow-2xl p-4 border border-white/10">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm"><div><div class="text-xs text-slate-400">Bersih Harian</div><div class="font-black" id="totalDailyNetLabel">Rp0</div></div><div><div class="text-xs text-slate-400">Operasional</div><div class="font-black text-amber-200" id="totalOperationalLabel">Rp0</div></div><div><div class="text-xs text-slate-400">Setelah Op.</div><div class="font-black text-cyan-200" id="totalAfterOperationalLabel">Rp0</div></div><div><div class="text-xs text-slate-400">Jasa Kapten</div><div class="font-black" id="totalCaptainShareLabel">Rp0</div></div><div><div class="text-xs text-slate-400">Owner Final</div><div class="font-black text-emerald-300" id="totalOwnerShareLabel">Rp0</div></div></div>
</div>

<div class="rounded-[1.75rem] bg-white p-4 md:p-5 shadow-sm border border-slate-100 space-y-4"><div><label class="block text-sm font-bold mb-2">Catatan Tutup Bulan</label><textarea name="notes" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-base min-h-28" placeholder="Catatan tambahan untuk rekap final owner">{{ old('notes') }}</textarea></div><button class="w-full md:w-auto min-h-[54px] bg-teal-600 text-white px-6 rounded-2xl font-black shadow-lg shadow-teal-700/20" @disabled($preview['total_invoices'] < 1)>Simpan Rekap Final Owner</button></div>
</form>
<script>
function formatRp(n){ return 'Rp' + Number(Math.round(n || 0)).toLocaleString('id-ID'); }
function recalcClosing(){ let totalDailyNet = 0, totalOperational = 0, totalAfterOperational = 0, totalCaptain = 0, totalOwner = 0; document.querySelectorAll('.ship-card').forEach(card => { const dailyNet = Number(card.dataset.dailyNet || 0); let operational = 0; card.querySelectorAll('.operational-amount').forEach(input => operational += Number(input.value || 0)); const afterOperational = dailyNet - operational; const range = card.querySelector('.captain-range'); const percent = Number(range.value || 0); const captain = Math.round(Math.max(0, afterOperational) * percent / 100); const owner = afterOperational - captain; totalDailyNet += dailyNet; totalOperational += operational; totalAfterOperational += afterOperational; totalCaptain += captain; totalOwner += owner; card.querySelector('.captain-percent-label').innerText = percent; card.querySelector('.operational-total-label').innerText = formatRp(operational); card.querySelector('.after-operational-label').innerText = formatRp(afterOperational); card.querySelector('.captain-share-label').innerText = formatRp(captain); card.querySelector('.owner-share-label').innerText = formatRp(owner); }); document.getElementById('totalDailyNetLabel').innerText = formatRp(totalDailyNet); document.getElementById('totalOperationalLabel').innerText = formatRp(totalOperational); document.getElementById('totalAfterOperationalLabel').innerText = formatRp(totalAfterOperational); document.getElementById('totalCaptainShareLabel').innerText = formatRp(totalCaptain); document.getElementById('totalOwnerShareLabel').innerText = formatRp(totalOwner); }
document.querySelectorAll('.captain-range, .operational-amount').forEach(el => el.addEventListener('input', recalcClosing));
document.querySelectorAll('.add-expense').forEach(button => { button.addEventListener('click', () => { const shipId = button.dataset.shipId; const list = document.getElementById('operational-list-' + shipId); const index = list.querySelectorAll('.operational-row').length; const row = document.createElement('div'); row.className = 'grid grid-cols-12 gap-2 operational-row'; row.innerHTML = `<input name="ships[${shipId}][operational_expenses][${index}][description]" class="col-span-7 min-h-[48px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base" placeholder="Keterangan"><input type="number" min="0" name="ships[${shipId}][operational_expenses][${index}][amount]" value="0" class="col-span-5 min-h-[48px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base operational-amount" placeholder="Nominal">`; list.appendChild(row); row.querySelector('.operational-amount').addEventListener('input', recalcClosing); }); }); recalcClosing();
</script>
@endif
@endsection
