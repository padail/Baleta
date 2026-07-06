@extends('layouts.app')
@section('title', 'Detail Invoice')
@section('content')
@php
    $detailBoxes = (int) $invoice->items->sum('box_count');
    $remainingBoxes = (int) $invoice->total_boxes - $detailBoxes;
@endphp
<div class="mb-5 flex flex-col md:flex-row md:items-start md:justify-between gap-3">
    <div><a href="{{ route('invoices.index') }}" class="inline-flex text-sm font-semibold text-slate-500 mb-3">← Daftar invoice</a><h1 class="text-2xl md:text-3xl font-black">{{ $invoice->invoice_number }}</h1><p class="text-sm text-slate-500 mt-1">{{ $invoice->invoice_date->format('d/m/Y') }} · {{ $invoice->ship?->name }} · {{ $invoice->status_label }}</p></div>
    <div class="grid grid-cols-2 md:flex gap-2">
        @if($invoice->status === 'draft')<a href="{{ route('invoices.edit', $invoice) }}" class="rounded-2xl bg-[#073b3a] px-4 py-3 text-center text-sm font-bold text-white">Edit</a><form method="POST" action="{{ route('invoices.post', $invoice) }}">@csrf<button class="w-full rounded-2xl bg-green-600 px-4 py-3 text-sm font-bold text-white">Posting</button></form>@endif
        @if(in_array($invoice->status, ['draft','posted']))<form method="POST" action="{{ route('invoices.cancel', $invoice) }}" onsubmit="return confirm('Batalkan invoice ini?')">@csrf<button class="w-full rounded-2xl bg-rose-600 px-4 py-3 text-sm font-bold text-white">Batal</button></form>@endif
        <a href="{{ route('invoices.screenshot', $invoice) }}" target="_blank" class="rounded-2xl bg-teal-600 px-4 py-3 text-center text-sm font-bold text-white">Screenshot</a>
    </div>
</div>
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100"><div class="text-xs text-slate-500">Kapal</div><div class="font-black">{{ $invoice->ship?->name }}</div></div>
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100"><div class="text-xs text-slate-500">Kapten</div><div class="font-black">{{ $invoice->captain?->name }}</div></div>
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100"><div class="text-xs text-slate-500">Gabus Turun</div><div class="font-black">{{ $invoice->total_boxes }}</div></div>
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100"><div class="text-xs text-slate-500">Gabus Dibeli</div><div class="font-black">{{ $detailBoxes }}</div><div class="text-xs text-slate-500">Sisa: {{ $remainingBoxes }}</div></div>
    <div class="rounded-[1.5rem] bg-[#073b3a] p-4 shadow-sm text-white col-span-2 md:col-span-1"><div class="text-xs text-slate-400">Bersih</div><div class="font-black text-xl text-emerald-300">{{ \App\Support\Money::rupiah($invoice->net_income) }}</div></div>
</div>
<div class="grid lg:grid-cols-2 gap-5">
    <div class="rounded-[1.5rem] bg-white shadow-sm border border-slate-100 overflow-hidden"><div class="p-4 border-b font-black">Pembeli</div><div class="divide-y divide-slate-100">@foreach($invoice->items as $item)<div class="p-4 flex justify-between gap-3 text-sm"><div><div class="font-bold">{{ $item->display_buyer_name }}</div><div class="text-xs text-slate-500">{{ $item->fish_type ?: 'Jenis ikan tidak diisi' }} · {{ $item->box_count }} gabus × {{ \App\Support\Money::rupiah($item->price_per_box) }}</div></div><div class="font-black whitespace-nowrap">{{ \App\Support\Money::rupiah($item->subtotal) }}</div></div>@endforeach</div></div>
    <div class="rounded-[1.5rem] bg-white shadow-sm border border-slate-100 overflow-hidden"><div class="p-4 border-b font-black">Pengeluaran Harian</div><div class="divide-y divide-slate-100">@foreach($invoice->expenses as $expense)<div class="p-4 flex justify-between gap-3 text-sm"><div><div class="font-bold capitalize">{{ $expense->expense_type }}</div><div class="text-xs text-slate-500">{{ $expense->description }} · Qty {{ $expense->quantity }}</div></div><div class="font-black text-amber-700 whitespace-nowrap">{{ \App\Support\Money::rupiah($expense->amount) }}</div></div>@endforeach</div></div>
</div>
<div class="mt-5 rounded-[1.5rem] bg-white shadow-sm border border-slate-100 p-4 grid grid-cols-3 gap-3 text-sm"><div><div class="text-xs text-slate-500">Pemasukan</div><div class="font-black">{{ \App\Support\Money::rupiah($invoice->total_income) }}</div></div><div><div class="text-xs text-slate-500">Pengeluaran</div><div class="font-black text-amber-700">{{ \App\Support\Money::rupiah($invoice->total_expense) }}</div></div><div><div class="text-xs text-slate-500">Bersih</div><div class="font-black text-green-700">{{ \App\Support\Money::rupiah($invoice->net_income) }}</div></div></div>
@endsection
