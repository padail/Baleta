@extends('layouts.app')
@section('title', 'Buat Tutup Bulan')
@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-bold">Buat Tutup Bulan</h1>
    <p class="text-sm text-slate-500 mt-1">Alur: invoice harian per kapal, rekap bulanan per kapal, lalu rekap final owner.</p>
</div>

@if ($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <div class="font-semibold mb-1">Periksa kembali data tutup bulan.</div>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="GET" action="{{ route('monthly-closings.create') }}" class="bg-white rounded-2xl shadow p-4 mb-5 grid grid-cols-2 md:grid-cols-4 gap-3">
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Bulan</label>
        <input type="number" name="month" min="1" max="12" value="{{ request('month', now()->month) }}" required class="w-full rounded-xl border-slate-300 text-base" placeholder="Bulan">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Tahun</label>
        <input type="number" name="year" value="{{ request('year', now()->year) }}" required class="w-full rounded-xl border-slate-300 text-base" placeholder="Tahun">
    </div>
    <div class="col-span-2 md:col-span-2 flex items-end">
        <button class="w-full rounded-xl bg-slate-900 text-white px-4 py-3 font-semibold">Preview Rekap Kapal</button>
    </div>
</form>

@if($preview)
<form method="POST" action="{{ route('monthly-closings.store') }}" class="space-y-5" id="closingForm">
@csrf
<input type="hidden" name="month" value="{{ request('month') }}">
<input type="hidden" name="year" value="{{ request('year') }}">

<div class="grid grid-cols-2 md:grid-cols-5 gap-3">
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Kapal Direkap</div><div class="text-xl font-bold">{{ $preview['total_ships'] }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Invoice Harian</div><div class="text-xl font-bold">{{ $preview['total_invoices'] }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Gabus Tercatat</div><div class="text-xl font-bold">{{ $preview['total_boxes'] }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4 col-span-2 md:col-span-1"><div class="text-xs text-slate-500">Bersih Harian</div><div class="text-xl font-bold text-blue-700">{{ \App\Support\Money::rupiah($preview['daily_net_income']) }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4 col-span-2 md:col-span-1"><div class="text-xs text-slate-500">Non-Operasional</div><div class="text-xl font-bold text-red-700">{{ \App\Support\Money::rupiah($preview['non_operational_expense_total']) }}</div></div>
</div>

<div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-sm text-blue-900">
    <div class="font-semibold">Cara hitung tutup bulan</div>
    <div class="mt-1">Setiap kapal dihitung sendiri. Bersih harian kapal dikurangi operasional bulanan kapal. Setelah itu baru dihitung jasa kapten. Hasil owner dari semua kapal digabung, lalu dikurangi pengeluaran non-operasional owner.</div>
</div>

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
    <section class="bg-white rounded-2xl shadow overflow-hidden ship-card" data-ship-id="{{ $ship['ship_id'] }}" data-daily-net="{{ (int) $ship['total_daily_net_income'] }}">
        <div class="p-4 border-b bg-slate-50">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $ship['ship_name'] }}</h2>
                    <p class="text-sm text-slate-500">Kapten: {{ $ship['captain_name'] }} · {{ $ship['total_invoices'] }} invoice harian</p>
                </div>
                <div class="text-left md:text-right">
                    <div class="text-xs text-slate-500">Bersih Harian Kapal</div>
                    <div class="text-xl font-bold text-blue-700">{{ \App\Support\Money::rupiah($ship['total_daily_net_income']) }}</div>
                </div>
            </div>
        </div>

        <div class="p-4 grid lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2 space-y-3">
                <div class="rounded-xl border overflow-hidden">
                    <div class="px-3 py-2 bg-slate-50 font-semibold text-sm">Invoice Harian Kapal</div>
                    <div class="divide-y">
                        @foreach($ship['invoices'] as $invoice)
                            <div class="p-3 flex justify-between gap-3 text-sm">
                                <div>
                                    <div class="font-semibold">{{ $invoice->invoice_number }}</div>
                                    <div class="text-xs text-slate-500">{{ $invoice->invoice_date->format('d/m/Y') }} · {{ $invoice->total_boxes }} gabus</div>
                                </div>
                                <div class="text-right font-semibold whitespace-nowrap">{{ \App\Support\Money::rupiah($invoice->net_income) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3 space-y-4">
                <div class="rounded-xl border p-3">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div>
                            <h3 class="font-semibold">Biaya Operasional Kapal</h3>
                            <p class="text-xs text-slate-500">Contoh: kebutuhan pokok nelayan, solar, air, es, bekal melaut.</p>
                        </div>
                        <button type="button" class="add-expense px-3 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold" data-ship-id="{{ $ship['ship_id'] }}">Tambah</button>
                    </div>
                    <div class="space-y-2 operational-list" id="operational-list-{{ $ship['ship_id'] }}">
                        @foreach($oldExpenses as $index => $expense)
                            <div class="grid grid-cols-12 gap-2 operational-row">
                                <input name="ships[{{ $ship['ship_id'] }}][operational_expenses][{{ $index }}][description]" value="{{ $expense['description'] ?? '' }}" class="col-span-7 rounded-lg border-slate-300 text-sm" placeholder="Keterangan">
                                <input type="number" min="0" name="ships[{{ $ship['ship_id'] }}][operational_expenses][{{ $index }}][amount]" value="{{ $expense['amount'] ?? 0 }}" class="col-span-5 rounded-lg border-slate-300 text-sm operational-amount" placeholder="Nominal">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border p-3 bg-slate-50">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-semibold">Jasa Kapten</label>
                        <div class="font-bold text-blue-700"><span class="captain-percent-label">{{ $oldPercentage }}</span>%</div>
                    </div>
                    <input type="range" min="0" max="100" step="1" value="{{ $oldPercentage }}" class="captain-range w-full" name="ships[{{ $ship['ship_id'] }}][captain_percentage]">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-3 text-sm">
                        <div class="bg-white rounded-lg p-3"><div class="text-xs text-slate-500">Operasional</div><div class="font-bold operational-total-label">Rp0</div></div>
                        <div class="bg-white rounded-lg p-3"><div class="text-xs text-slate-500">Setelah Op.</div><div class="font-bold after-operational-label">Rp0</div></div>
                        <div class="bg-white rounded-lg p-3"><div class="text-xs text-slate-500">Kapten</div><div class="font-bold captain-share-label">Rp0</div></div>
                        <div class="bg-white rounded-lg p-3"><div class="text-xs text-slate-500">Owner Kapal</div><div class="font-bold owner-share-label">Rp0</div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@empty
    <div class="bg-white rounded-2xl shadow p-8 text-center text-slate-500">Tidak ada invoice posted untuk periode ini.</div>
@endforelse
</div>

<div class="bg-white rounded-2xl shadow p-4 sticky bottom-20 md:bottom-4 z-10 border border-slate-200">
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">
        <div><div class="text-xs text-slate-500">Bersih Harian</div><div class="font-bold" id="totalDailyNetLabel">Rp0</div></div>
        <div><div class="text-xs text-slate-500">Operasional Kapal</div><div class="font-bold text-orange-700" id="totalOperationalLabel">Rp0</div></div>
        <div><div class="text-xs text-slate-500">Setelah Op.</div><div class="font-bold text-blue-700" id="totalAfterOperationalLabel">Rp0</div></div>
        <div><div class="text-xs text-slate-500">Jasa Kapten</div><div class="font-bold" id="totalCaptainShareLabel">Rp0</div></div>
        <div><div class="text-xs text-slate-500">Owner Kapal</div><div class="font-bold" id="totalOwnerShareLabel">Rp0</div></div>
        <div><div class="text-xs text-slate-500">Final Owner</div><div class="font-bold text-green-700" id="ownerFinalLabel">Rp0</div></div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow p-4 space-y-4">
    <div>
        <h3 class="font-semibold">Pengeluaran Non-Operasional Owner</h3>
        <p class="text-xs text-slate-500 mt-1">Tidak mempengaruhi hasil kapal dan jasa kapten. Hanya mengurangi saldo akhir owner.</p>
    </div>
    <div class="rounded-xl border overflow-hidden">
        <div class="divide-y">
            @forelse($preview['non_operational_expenses'] as $expense)
                <div class="p-3 flex justify-between gap-3 text-sm">
                    <div>
                        <div class="font-semibold">{{ $expense->description }}</div>
                        <div class="text-xs text-slate-500">{{ $expense->expense_date->format('d/m/Y') }}</div>
                    </div>
                    <div class="font-semibold text-red-700 whitespace-nowrap">{{ \App\Support\Money::rupiah($expense->amount) }}</div>
                </div>
            @empty
                <div class="p-4 text-sm text-slate-500">Belum ada pengeluaran non-operasional untuk periode ini.</div>
            @endforelse
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Catatan Tutup Bulan</label>
        <textarea name="notes" class="w-full rounded-xl border-slate-300 min-h-28" placeholder="Catatan tambahan untuk rekap final owner">{{ old('notes') }}</textarea>
    </div>
    <button class="w-full md:w-auto bg-blue-600 text-white px-5 py-3 rounded-xl font-semibold" @disabled($preview['total_invoices'] < 1)>Simpan Rekap Final Owner</button>
</div>
</form>

<script>
const nonOperationalTotal = Number({{ (int) $preview['non_operational_expense_total'] }});
function formatRp(n){ return 'Rp' + Number(Math.round(n || 0)).toLocaleString('id-ID'); }
function recalcClosing(){
    let totalDailyNet = 0, totalOperational = 0, totalAfterOperational = 0, totalCaptain = 0, totalOwner = 0;
    document.querySelectorAll('.ship-card').forEach(card => {
        const dailyNet = Number(card.dataset.dailyNet || 0);
        let operational = 0;
        card.querySelectorAll('.operational-amount').forEach(input => operational += Number(input.value || 0));
        const afterOperational = dailyNet - operational;
        const range = card.querySelector('.captain-range');
        const percent = Number(range.value || 0);
        const captain = Math.round(Math.max(0, afterOperational) * percent / 100);
        const owner = afterOperational - captain;
        totalDailyNet += dailyNet;
        totalOperational += operational;
        totalAfterOperational += afterOperational;
        totalCaptain += captain;
        totalOwner += owner;
        card.querySelector('.captain-percent-label').innerText = percent;
        card.querySelector('.operational-total-label').innerText = formatRp(operational);
        card.querySelector('.after-operational-label').innerText = formatRp(afterOperational);
        card.querySelector('.captain-share-label').innerText = formatRp(captain);
        card.querySelector('.owner-share-label').innerText = formatRp(owner);
    });
    document.getElementById('totalDailyNetLabel').innerText = formatRp(totalDailyNet);
    document.getElementById('totalOperationalLabel').innerText = formatRp(totalOperational);
    document.getElementById('totalAfterOperationalLabel').innerText = formatRp(totalAfterOperational);
    document.getElementById('totalCaptainShareLabel').innerText = formatRp(totalCaptain);
    document.getElementById('totalOwnerShareLabel').innerText = formatRp(totalOwner);
    document.getElementById('ownerFinalLabel').innerText = formatRp(totalOwner - nonOperationalTotal);
}
document.querySelectorAll('.captain-range, .operational-amount').forEach(el => el.addEventListener('input', recalcClosing));
document.querySelectorAll('.add-expense').forEach(button => {
    button.addEventListener('click', () => {
        const shipId = button.dataset.shipId;
        const list = document.getElementById('operational-list-' + shipId);
        const index = list.querySelectorAll('.operational-row').length;
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-2 operational-row';
        row.innerHTML = `<input name="ships[${shipId}][operational_expenses][${index}][description]" class="col-span-7 rounded-lg border-slate-300 text-sm" placeholder="Keterangan"><input type="number" min="0" name="ships[${shipId}][operational_expenses][${index}][amount]" value="0" class="col-span-5 rounded-lg border-slate-300 text-sm operational-amount" placeholder="Nominal">`;
        list.appendChild(row);
        row.querySelector('.operational-amount').addEventListener('input', recalcClosing);
    });
});
recalcClosing();
</script>
@endif
@endsection
