@extends('layouts.app')
@section('title', 'Invoice Pengiriman')
@section('content')
<div class="flex flex-wrap justify-between items-center gap-3 mb-5"><h1 class="text-2xl font-bold">Invoice Pengiriman Ikan</h1><a href="{{ route('invoices.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Tambah Invoice</a></div>
<form method="GET" class="bg-white rounded-xl shadow p-4 mb-4 grid md:grid-cols-5 gap-3 text-sm">
<select name="ship_id" class="rounded-lg border-slate-300"><option value="">Semua kapal</option>@foreach($ships as $ship)<option value="{{ $ship->id }}" @selected(request('ship_id') == $ship->id)>{{ $ship->name }}</option>@endforeach</select>
<select name="status" class="rounded-lg border-slate-300"><option value="">Semua status</option>@foreach(['draft','posted','closed','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ strtoupper($status) }}</option>@endforeach</select>
<input type="number" name="month" value="{{ request('month') }}" min="1" max="12" placeholder="Bulan" class="rounded-lg border-slate-300">
<input type="number" name="year" value="{{ request('year', now()->year) }}" placeholder="Tahun" class="rounded-lg border-slate-300">
<button class="rounded-lg bg-slate-800 text-white px-4 py-2">Filter</button>
</form>
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">Nomor</th><th class="p-3 text-left">Tanggal</th><th class="p-3 text-left">Kapal</th><th class="p-3 text-right">Gabus</th><th class="p-3 text-right">Pemasukan</th><th class="p-3 text-right">Pengeluaran</th><th class="p-3 text-right">Bersih</th><th class="p-3 text-left">Status</th></tr></thead><tbody>
@forelse($invoices as $invoice)<tr class="border-t"><td class="p-3"><a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 font-semibold">{{ $invoice->invoice_number }}</a></td><td class="p-3">{{ $invoice->invoice_date->format('d/m/Y') }}</td><td class="p-3">{{ $invoice->ship?->name }}</td><td class="p-3 text-right">{{ $invoice->total_boxes }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($invoice->total_income) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($invoice->total_expense) }}</td><td class="p-3 text-right font-semibold">{{ \App\Support\Money::rupiah($invoice->net_income) }}</td><td class="p-3">{{ strtoupper($invoice->status) }}</td></tr>@empty<tr><td colspan="8" class="p-6 text-center text-slate-500">Belum ada invoice.</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $invoices->links() }}</div>
@endsection
