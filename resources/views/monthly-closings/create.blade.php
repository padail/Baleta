@extends('layouts.app')
@section('title', 'Buat Tutup Bulan')
@section('content')
<h1 class="text-2xl font-bold mb-2">Buat Tutup Bulan</h1>
<p class="text-sm text-slate-500 mb-5">Grand invoice bulanan mengambil semua invoice posted dari semua kapal, lalu mengurangi biaya operasional bulanan sebelum pembagian jasa kapten.</p>

<form method="GET" action="{{ route('monthly-closings.create') }}" class="bg-white rounded-xl shadow p-5 mb-5 grid md:grid-cols-3 gap-4">
    <input type="number" name="month" min="1" max="12" value="{{ request('month', now()->month) }}" required class="rounded-lg border-slate-300" placeholder="Bulan">
    <input type="number" name="year" value="{{ request('year', now()->year) }}" required class="rounded-lg border-slate-300" placeholder="Tahun">
    <button class="rounded-lg bg-slate-800 text-white px-4 py-2">Preview Semua Kapal</button>
</form>

@if($preview)
<form method="POST" action="{{ route('monthly-closings.store') }}" class="bg-white rounded-xl shadow p-5 space-y-5">
@csrf
<input type="hidden" name="month" value="{{ request('month') }}">
<input type="hidden" name="year" value="{{ request('year') }}">

<div class="grid md:grid-cols-6 gap-4">
    <div><div class="text-xs text-slate-500">Kapal</div><div class="font-bold">{{ $preview['total_ships'] }}</div></div>
    <div><div class="text-xs text-slate-500">Invoice</div><div class="font-bold">{{ $preview['total_invoices'] }}</div></div>
    <div><div class="text-xs text-slate-500">Gabus</div><div class="font-bold">{{ $preview['total_boxes'] }}</div></div>
    <div><div class="text-xs text-slate-500">Pemasukan Kotor</div><div class="font-bold">{{ \App\Support\Money::rupiah($preview['total_income']) }}</div></div>
    <div><div class="text-xs text-slate-500">Ongkir + Gabus</div><div class="font-bold">{{ \App\Support\Money::rupiah($preview['total_expense']) }}</div></div>
    <div><div class="text-xs text-slate-500">Bersih Harian</div><div class="font-bold">{{ \App\Support\Money::rupiah($preview['daily_net_income']) }}</div></div>
</div>

<div class="grid md:grid-cols-4 gap-4">
    <div class="rounded-xl border p-4 bg-slate-50">
        <div class="text-xs text-slate-500">Bersih Harian Invoice</div>
        <div class="text-lg font-bold">{{ \App\Support\Money::rupiah($preview['daily_net_income']) }}</div>
        <p class="text-xs text-slate-500 mt-1">Sudah dikurangi ongkir kapal dan jasa angkat gabus.</p>
    </div>
    <div class="rounded-xl border p-4 bg-orange-50">
        <div class="text-xs text-orange-700">Biaya Operasional Bulanan</div>
        <div class="text-lg font-bold text-orange-800">{{ \App\Support\Money::rupiah($preview['operational_expense_total']) }}</div>
        <p class="text-xs text-orange-700 mt-1">Mengurangi dasar pembagian kapten-owner.</p>
    </div>
    <div class="rounded-xl border p-4 bg-blue-50">
        <div class="text-xs text-blue-700">Dasar Pembagian</div>
        <div class="text-lg font-bold text-blue-800" id="distributable_label">{{ \App\Support\Money::rupiah($preview['distributable_income']) }}</div>
        <p class="text-xs text-blue-700 mt-1">Bersih harian dikurangi biaya operasional bulanan.</p>
    </div>
    <div class="rounded-xl border p-4 bg-red-50">
        <div class="text-xs text-red-700">Non-Operasional</div>
        <div class="text-lg font-bold text-red-800">{{ \App\Support\Money::rupiah($preview['non_operational_expense_total']) }}</div>
        <p class="text-xs text-red-700 mt-1">Hanya masuk rekap, tidak mengurangi dasar pembagian.</p>
    </div>
</div>

<div class="rounded-xl border bg-slate-50 p-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold">Pengeluaran Rekap Periode Ini</h2>
            <p class="text-sm text-slate-500">Pengeluaran diambil dari menu Pengeluaran Rekap dengan status posted.</p>
        </div>
        <a href="{{ route('expenses.create') }}" class="px-3 py-2 bg-white border rounded-lg text-sm">Tambah Pengeluaran</a>
    </div>
    <div class="grid md:grid-cols-2 gap-4 mt-4">
        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="p-3 bg-orange-50 font-semibold text-sm">Operasional Bulanan</div>
            <table class="min-w-full text-sm"><tbody>
            @forelse($preview['operational_expenses'] as $expense)
                <tr class="border-t"><td class="p-3">{{ $expense->expense_date->format('d/m/Y') }}</td><td class="p-3">{{ $expense->description }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($expense->amount) }}</td></tr>
            @empty
                <tr><td class="p-4 text-slate-500">Tidak ada biaya operasional.</td></tr>
            @endforelse
            </tbody></table>
        </div>
        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="p-3 bg-red-50 font-semibold text-sm">Non-Operasional</div>
            <table class="min-w-full text-sm"><tbody>
            @forelse($preview['non_operational_expenses'] as $expense)
                <tr class="border-t"><td class="p-3">{{ $expense->expense_date->format('d/m/Y') }}</td><td class="p-3">{{ $expense->description }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($expense->amount) }}</td></tr>
            @empty
                <tr><td class="p-4 text-slate-500">Tidak ada pengeluaran non-operasional.</td></tr>
            @endforelse
            </tbody></table>
        </div>
    </div>
</div>

<div class="border rounded-xl overflow-hidden">
    <div class="p-3 bg-slate-50 font-semibold">Rekap per Kapal</div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50"><tr><th class="p-3 text-left">Kapal</th><th class="p-3 text-left">Kapten</th><th class="p-3 text-right">Invoice</th><th class="p-3 text-right">Gabus</th><th class="p-3 text-right">Bersih Harian</th><th class="p-3 text-right">Alokasi Operasional</th><th class="p-3 text-right">Dasar Bagi</th><th class="p-3 text-right">Estimasi Kapten</th></tr></thead>
            <tbody>
                @forelse($preview['ship_summaries'] as $ship)
                    <tr class="border-t ship-summary-row" data-net="{{ $ship['distributable_income'] }}">
                        <td class="p-3 font-semibold">{{ $ship['ship_name'] }}</td><td class="p-3">{{ $ship['captain_name'] }}</td><td class="p-3 text-right">{{ $ship['total_invoices'] }}</td><td class="p-3 text-right">{{ $ship['total_boxes'] }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($ship['net_income']) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($ship['operational_expense']) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($ship['distributable_income']) }}</td><td class="p-3 text-right ship-captain-share">Rp0</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-6 text-center text-slate-500">Tidak ada invoice posted.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="overflow-x-auto border rounded-xl">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50"><tr><th class="p-3 text-left">Invoice</th><th class="p-3 text-left">Tanggal</th><th class="p-3 text-left">Kapal</th><th class="p-3 text-left">Kapten</th><th class="p-3 text-right">Gabus</th><th class="p-3 text-right">Bersih Harian</th></tr></thead>
        <tbody>
            @forelse($preview['invoices'] as $invoice)
                <tr class="border-t"><td class="p-3">{{ $invoice->invoice_number }}</td><td class="p-3">{{ $invoice->invoice_date->format('d/m/Y') }}</td><td class="p-3">{{ $invoice->ship?->name }}</td><td class="p-3">{{ $invoice->captain?->name }}</td><td class="p-3 text-right">{{ $invoice->total_boxes }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($invoice->net_income) }}</td></tr>
            @empty
                <tr><td colspan="6" class="p-6 text-center text-slate-500">Tidak ada invoice posted.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="grid md:grid-cols-2 gap-4">
    <div class="rounded-xl border p-4 bg-slate-50">
        <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium">Persentase Jasa Kapten</label>
            <div class="font-bold text-blue-700" id="captain_percent_label">20%</div>
        </div>
        <input type="range" min="0" max="100" step="1" value="{{ old('captain_percentage', 20) }}" id="captain_percentage_range" class="w-full">
        <input type="hidden" name="captain_percentage" value="{{ old('captain_percentage', 20) }}" id="captain_percentage">
        <div class="grid grid-cols-3 gap-3 mt-4">
            <div class="bg-white rounded-lg p-3"><div class="text-xs text-slate-500">Jasa Kapten</div><div class="font-bold" id="captain_share_label">Rp0</div></div>
            <div class="bg-white rounded-lg p-3"><div class="text-xs text-slate-500">Owner Sebelum Non-Op</div><div class="font-bold" id="owner_share_label">Rp0</div></div>
            <div class="bg-white rounded-lg p-3"><div class="text-xs text-slate-500">Owner Final</div><div class="font-bold" id="owner_final_label">Rp0</div></div>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Catatan</label>
        <textarea name="notes" class="w-full rounded-lg border-slate-300 min-h-32">{{ old('notes') }}</textarea>
    </div>
</div>
<button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold" @disabled($preview['total_invoices'] < 1)>Buat Grand Invoice Bulanan</button>
</form>
<script>
const distributableIncome = Number({{ (int) $preview['distributable_income'] }});
const nonOperationalTotal = Number({{ (int) $preview['non_operational_expense_total'] }});
function formatRp(n){ return 'Rp' + Number(Math.round(n || 0)).toLocaleString('id-ID'); }
function recalcCaptainShare(){
    const range = document.getElementById('captain_percentage_range');
    const percentage = Number(range.value || 0);
    const captainBase = Math.max(0, distributableIncome);
    const captainShare = Math.round(captainBase * percentage / 100);
    const ownerShare = distributableIncome - captainShare;
    const ownerFinal = ownerShare - nonOperationalTotal;
    document.getElementById('captain_percentage').value = percentage;
    document.getElementById('captain_percent_label').innerText = percentage + '%';
    document.getElementById('captain_share_label').innerText = formatRp(captainShare);
    document.getElementById('owner_share_label').innerText = formatRp(ownerShare);
    document.getElementById('owner_final_label').innerText = formatRp(ownerFinal);
    document.querySelectorAll('.ship-summary-row').forEach(row => {
        const shipNet = Number(row.dataset.net || 0);
        row.querySelector('.ship-captain-share').innerText = formatRp(Math.max(0, shipNet) * percentage / 100);
    });
}
document.getElementById('captain_percentage_range')?.addEventListener('input', recalcCaptainShare);
recalcCaptainShare();
</script>
@endif
@endsection
