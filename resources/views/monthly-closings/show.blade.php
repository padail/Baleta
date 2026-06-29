@extends('layouts.app')
@section('title', 'Detail Tutup Bulan')
@section('content')
@php
    $shipGroups = $closing->items->groupBy(fn($item) => $item->ship_id ?: $item->ship_name);
    $operationalExpenses = $closing->expenses->where('expense_type', \App\Models\OwnerExpense::TYPE_OPERATIONAL);
    $nonOperationalExpenses = $closing->expenses->where('expense_type', \App\Models\OwnerExpense::TYPE_NON_OPERATIONAL);
@endphp
<div class="flex flex-wrap justify-between items-center gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">{{ $closing->closing_number }}</h1>
        <p class="text-sm text-slate-500">Periode {{ $closing->month }}/{{ $closing->year }} · {{ strtoupper($closing->status) }}</p>
    </div>
    <a target="_blank" href="{{ route('monthly-closings.print', $closing) }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">Print</a>
</div>

<div class="grid md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-slate-500">Total Kapal</div><div class="font-bold">{{ $closing->total_ships }}</div></div>
    <div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-slate-500">Total Invoice</div><div class="font-bold">{{ $closing->total_invoices }}</div></div>
    <div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-slate-500">Persen Jasa Kapten</div><div class="font-bold">{{ $closing->captain_percentage }}%</div></div>
    <div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-slate-500">Total Gabus</div><div class="font-bold">{{ $closing->total_boxes }}</div></div>
</div>

<div class="bg-white rounded-xl shadow p-5 mb-6">
    <h2 class="font-semibold mb-4">Ringkasan Grand Invoice</h2>
    <div class="grid md:grid-cols-4 gap-4">
        <div><div class="text-xs text-slate-500">Pemasukan Kotor</div><div class="font-bold">{{ \App\Support\Money::rupiah($closing->total_income) }}</div></div>
        <div><div class="text-xs text-slate-500">Ongkir + Jasa Angkat Gabus</div><div class="font-bold">{{ \App\Support\Money::rupiah($closing->total_expense) }}</div></div>
        <div><div class="text-xs text-slate-500">Bersih Harian</div><div class="font-bold">{{ \App\Support\Money::rupiah($closing->daily_net_income ?: $closing->net_income) }}</div></div>
        <div><div class="text-xs text-slate-500">Operasional Bulanan</div><div class="font-bold text-orange-700">{{ \App\Support\Money::rupiah($closing->operational_expense_total) }}</div></div>
        <div><div class="text-xs text-slate-500">Dasar Pembagian</div><div class="font-bold text-blue-700">{{ \App\Support\Money::rupiah($closing->distributable_income) }}</div></div>
        <div><div class="text-xs text-slate-500">Jasa Kapten</div><div class="font-bold">{{ \App\Support\Money::rupiah($closing->captain_share) }}</div></div>
        <div><div class="text-xs text-slate-500">Bagian Owner</div><div class="font-bold">{{ \App\Support\Money::rupiah($closing->owner_share) }}</div></div>
        <div><div class="text-xs text-slate-500">Owner Final Setelah Non-Op</div><div class="font-bold text-red-700">{{ \App\Support\Money::rupiah($closing->owner_final_income) }}</div></div>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b font-semibold bg-orange-50">Biaya Operasional Bulanan</div>
        <table class="min-w-full text-sm"><tbody>
            @forelse($operationalExpenses as $expense)
                <tr class="border-t"><td class="p-3">{{ $expense->expense_date->format('d/m/Y') }}</td><td class="p-3">{{ $expense->ship?->name ?? 'Umum' }}</td><td class="p-3">{{ $expense->description }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($expense->amount) }}</td></tr>
            @empty
                <tr><td class="p-4 text-slate-500">Tidak ada biaya operasional.</td></tr>
            @endforelse
        </tbody></table>
    </div>
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b font-semibold bg-red-50">Pengeluaran Non-Operasional</div>
        <table class="min-w-full text-sm"><tbody>
            @forelse($nonOperationalExpenses as $expense)
                <tr class="border-t"><td class="p-3">{{ $expense->expense_date->format('d/m/Y') }}</td><td class="p-3">{{ $expense->ship?->name ?? 'Umum' }}</td><td class="p-3">{{ $expense->description }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($expense->amount) }}</td></tr>
            @empty
                <tr><td class="p-4 text-slate-500">Tidak ada pengeluaran non-operasional.</td></tr>
            @endforelse
        </tbody></table>
    </div>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden mb-6">
    <div class="p-4 border-b font-semibold">Rekap per Kapal</div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50"><tr><th class="p-3 text-left">Kapal</th><th class="p-3 text-left">Kapten</th><th class="p-3 text-right">Invoice</th><th class="p-3 text-right">Gabus</th><th class="p-3 text-right">Bersih Harian</th><th class="p-3 text-right">Operasional</th><th class="p-3 text-right">Dasar Bagi</th><th class="p-3 text-right">Jasa Kapten</th><th class="p-3 text-right">Owner</th></tr></thead>
            <tbody>@foreach($shipGroups as $group)<tr class="border-t"><td class="p-3 font-semibold">{{ $group->first()->ship_name ?? $group->first()->ship?->name }}</td><td class="p-3">{{ $group->first()->captain_name ?? $group->first()->captain?->name }}</td><td class="p-3 text-right">{{ $group->count() }}</td><td class="p-3 text-right">{{ $group->sum('total_boxes') }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($group->sum('net_income')) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($group->sum('operational_expense')) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($group->sum('distributable_income')) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($group->sum('captain_share')) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($group->sum('owner_share')) }}</td></tr>@endforeach</tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden mb-6">
    <div class="p-4 border-b font-semibold">Invoice Masuk Tutup Bulan</div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50"><tr><th class="p-3 text-left">Invoice</th><th class="p-3 text-left">Tanggal</th><th class="p-3 text-left">Kapal</th><th class="p-3 text-left">Kapten</th><th class="p-3 text-right">Gabus</th><th class="p-3 text-right">Pemasukan</th><th class="p-3 text-right">Ongkir+Gabus</th><th class="p-3 text-right">Bersih Harian</th><th class="p-3 text-right">Alokasi Op.</th><th class="p-3 text-right">Dasar Bagi</th></tr></thead>
            <tbody>@foreach($closing->items as $item)<tr class="border-t"><td class="p-3">{{ $item->invoice?->invoice_number }}</td><td class="p-3">{{ $item->invoice_date->format('d/m/Y') }}</td><td class="p-3">{{ $item->ship_name ?? $item->ship?->name }}</td><td class="p-3">{{ $item->captain_name ?? $item->captain?->name }}</td><td class="p-3 text-right">{{ $item->total_boxes }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($item->total_income) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($item->total_expense) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($item->net_income) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($item->operational_expense) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($item->distributable_income) }}</td></tr>@endforeach</tbody>
        </table>
    </div>
</div>
@endsection
