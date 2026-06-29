@extends('layouts.app')
@section('title', 'Detail Tutup Bulan')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">{{ $closing->closing_number }}</h1>
        <p class="text-sm text-slate-500">Periode {{ str_pad($closing->month, 2, '0', STR_PAD_LEFT) }}/{{ $closing->year }} · Rekap final owner</p>
    </div>
    <a href="{{ route('monthly-closings.print', $closing) }}" target="_blank" class="px-4 py-3 bg-slate-900 text-white rounded-xl text-sm font-semibold text-center">Cetak</a>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Bersih Harian</div><div class="text-lg font-bold">{{ \App\Support\Money::rupiah($closing->daily_net_income) }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Operasional Kapal</div><div class="text-lg font-bold text-orange-700">{{ \App\Support\Money::rupiah($closing->operational_expense_total) }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Jasa Kapten</div><div class="text-lg font-bold">{{ \App\Support\Money::rupiah($closing->captain_share) }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Final Owner</div><div class="text-lg font-bold text-green-700">{{ \App\Support\Money::rupiah($closing->owner_final_income) }}</div></div>
</div>

<div class="space-y-4 mb-5">
@foreach($closing->shipItems as $shipItem)
    <section class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="p-4 bg-slate-50 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold">{{ $shipItem->ship_name }}</h2>
                <p class="text-sm text-slate-500">Kapten: {{ $shipItem->captain_name }} · {{ $shipItem->total_invoices }} invoice</p>
            </div>
            <div class="text-sm md:text-right">
                <div class="text-slate-500">Owner dari kapal</div>
                <div class="text-xl font-bold text-green-700">{{ \App\Support\Money::rupiah($shipItem->owner_share) }}</div>
            </div>
        </div>
        <div class="p-4 grid md:grid-cols-5 gap-3 text-sm">
            <div class="rounded-xl border p-3"><div class="text-xs text-slate-500">Bersih Harian</div><div class="font-bold">{{ \App\Support\Money::rupiah($shipItem->total_daily_net_income) }}</div></div>
            <div class="rounded-xl border p-3"><div class="text-xs text-slate-500">Operasional</div><div class="font-bold text-orange-700">{{ \App\Support\Money::rupiah($shipItem->total_ship_operational_expense) }}</div></div>
            <div class="rounded-xl border p-3"><div class="text-xs text-slate-500">Setelah Op.</div><div class="font-bold text-blue-700">{{ \App\Support\Money::rupiah($shipItem->net_after_ship_operational) }}</div></div>
            <div class="rounded-xl border p-3"><div class="text-xs text-slate-500">Kapten {{ $shipItem->captain_percentage }}%</div><div class="font-bold">{{ \App\Support\Money::rupiah($shipItem->captain_share) }}</div></div>
            <div class="rounded-xl border p-3"><div class="text-xs text-slate-500">Owner Kapal</div><div class="font-bold text-green-700">{{ \App\Support\Money::rupiah($shipItem->owner_share) }}</div></div>
        </div>
        <div class="p-4 grid md:grid-cols-2 gap-4">
            <div class="rounded-xl border overflow-hidden">
                <div class="p-3 bg-slate-50 font-semibold text-sm">Invoice Harian</div>
                <div class="divide-y">
                    @foreach($shipItem->invoiceItems as $invoiceItem)
                        <div class="p-3 flex justify-between gap-3 text-sm">
                            <div><div class="font-semibold">{{ $invoiceItem->invoice_number }}</div><div class="text-xs text-slate-500">{{ $invoiceItem->invoice_date->format('d/m/Y') }} · {{ $invoiceItem->total_boxes }} gabus</div></div>
                            <div class="font-semibold whitespace-nowrap">{{ \App\Support\Money::rupiah($invoiceItem->net_income) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-xl border overflow-hidden">
                <div class="p-3 bg-orange-50 font-semibold text-sm">Biaya Operasional Kapal</div>
                <div class="divide-y">
                    @forelse($shipItem->operationalExpenses as $expense)
                        <div class="p-3 flex justify-between gap-3 text-sm">
                            <div class="font-medium">{{ $expense->description }}</div>
                            <div class="font-semibold text-orange-700 whitespace-nowrap">{{ \App\Support\Money::rupiah($expense->amount) }}</div>
                        </div>
                    @empty
                        <div class="p-4 text-sm text-slate-500">Tidak ada biaya operasional kapal.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endforeach
</div>

<div class="bg-white rounded-2xl shadow p-4">
    <h2 class="font-bold mb-3">Ringkasan Final Owner</h2>
    <div class="space-y-2 text-sm">
        <div class="flex justify-between gap-3"><span>Total owner dari semua kapal</span><strong>{{ \App\Support\Money::rupiah($closing->owner_share) }}</strong></div>
        <div class="flex justify-between gap-3 text-red-700"><span>Pengeluaran non-operasional</span><strong>{{ \App\Support\Money::rupiah($closing->non_operational_expense_total) }}</strong></div>
        <div class="border-t pt-2 flex justify-between gap-3 text-green-700 text-base"><span class="font-bold">Saldo akhir owner</span><strong>{{ \App\Support\Money::rupiah($closing->owner_final_income) }}</strong></div>
    </div>
</div>
@endsection
