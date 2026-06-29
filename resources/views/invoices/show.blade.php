@extends('layouts.app')
@section('title', 'Detail Invoice')
@section('content')
@php
    $detailBoxes = (int) $invoice->items->sum('box_count');
    $remainingBoxes = (int) $invoice->total_boxes - $detailBoxes;
@endphp
<div class="flex flex-wrap justify-between items-center gap-3 mb-5">
    <div><h1 class="text-2xl font-bold">Invoice {{ $invoice->invoice_number }}</h1><p class="text-sm text-slate-500">{{ $invoice->invoice_date->format('d/m/Y') }} · {{ strtoupper($invoice->status) }}</p></div>
    <div class="flex gap-2">
        @if($invoice->status === 'draft')<a href="{{ route('invoices.edit', $invoice) }}" class="px-4 py-2 rounded-lg bg-slate-800 text-white text-sm">Edit</a><form method="POST" action="{{ route('invoices.post', $invoice) }}">@csrf<button class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm">Posting</button></form>@endif
        @if(in_array($invoice->status, ['draft','posted']))<form method="POST" action="{{ route('invoices.cancel', $invoice) }}" onsubmit="return confirm('Batalkan invoice ini?')">@csrf<button class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm">Cancel</button></form>@endif
        <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">Print</a>
    </div>
</div>
<div class="grid md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-slate-500">Kapal</div><div class="font-bold">{{ $invoice->ship?->name }}</div></div>
    <div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-slate-500">Kapten</div><div class="font-bold">{{ $invoice->captain?->name }}</div></div>
    <div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-slate-500">Gabus Turun</div><div class="font-bold">{{ $invoice->total_boxes }}</div></div>
    <div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-slate-500">Gabus Dibeli</div><div class="font-bold">{{ $detailBoxes }}</div><div class="text-xs text-slate-500">Sisa: {{ $remainingBoxes }}</div></div>
    <div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-slate-500">Bersih</div><div class="font-bold">{{ \App\Support\Money::rupiah($invoice->net_income) }}</div></div>
</div>
<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow overflow-hidden"><div class="p-4 border-b font-semibold">Pembeli</div><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">Pembeli</th><th class="p-3 text-left">Ikan</th><th class="p-3 text-right">Gabus</th><th class="p-3 text-right">Harga/Gabus</th><th class="p-3 text-right">Subtotal</th></tr></thead><tbody>@foreach($invoice->items as $item)<tr class="border-t"><td class="p-3">{{ $item->display_buyer_name }}</td><td class="p-3">{{ $item->fish_type }}</td><td class="p-3 text-right">{{ $item->box_count }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($item->price_per_box) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($item->subtotal) }}</td></tr>@endforeach</tbody></table></div>
    <div class="bg-white rounded-xl shadow overflow-hidden"><div class="p-4 border-b font-semibold">Pengeluaran</div><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">Jenis</th><th class="p-3 text-left">Deskripsi</th><th class="p-3 text-right">Qty</th><th class="p-3 text-right">Nominal</th></tr></thead><tbody>@foreach($invoice->expenses as $expense)<tr class="border-t"><td class="p-3">{{ $expense->expense_type }}</td><td class="p-3">{{ $expense->description }}</td><td class="p-3 text-right">{{ $expense->quantity }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($expense->amount) }}</td></tr>@endforeach</tbody></table></div>
</div>
<div class="bg-white rounded-xl shadow p-5 mt-6 grid md:grid-cols-3 gap-4"><div><div class="text-xs text-slate-500">Total Pemasukan</div><div class="font-bold">{{ \App\Support\Money::rupiah($invoice->total_income) }}</div></div><div><div class="text-xs text-slate-500">Total Pengeluaran</div><div class="font-bold">{{ \App\Support\Money::rupiah($invoice->total_expense) }}</div></div><div><div class="text-xs text-slate-500">Pendapatan Bersih</div><div class="font-bold">{{ \App\Support\Money::rupiah($invoice->net_income) }}</div></div></div>
@endsection
