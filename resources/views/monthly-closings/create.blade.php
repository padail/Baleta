@extends('layouts.app')
@section('title', 'Buat Tutup Bulan')
@section('content')
<h1 class="text-2xl font-bold mb-5">Buat Tutup Bulan</h1>
<form method="GET" action="{{ route('monthly-closings.create') }}" class="bg-white rounded-xl shadow p-5 mb-5 grid md:grid-cols-4 gap-4">
    <select name="ship_id" required class="rounded-lg border-slate-300"><option value="">Pilih kapal</option>@foreach($ships as $ship)<option value="{{ $ship->id }}" @selected(request('ship_id') == $ship->id)>{{ $ship->name }} · {{ $ship->activeCaptainAssignment?->captain?->name ?? '-' }}</option>@endforeach</select>
    <input type="number" name="month" min="1" max="12" value="{{ request('month', now()->month) }}" required class="rounded-lg border-slate-300">
    <input type="number" name="year" value="{{ request('year', now()->year) }}" required class="rounded-lg border-slate-300">
    <button class="rounded-lg bg-slate-800 text-white px-4 py-2">Preview</button>
</form>
@if($preview)
<form method="POST" action="{{ route('monthly-closings.store') }}" class="bg-white rounded-xl shadow p-5 space-y-5">
@csrf
<input type="hidden" name="ship_id" value="{{ request('ship_id') }}"><input type="hidden" name="month" value="{{ request('month') }}"><input type="hidden" name="year" value="{{ request('year') }}">
<div class="grid md:grid-cols-5 gap-4"><div><div class="text-xs text-slate-500">Invoice</div><div class="font-bold">{{ $preview['total_invoices'] }}</div></div><div><div class="text-xs text-slate-500">Gabus</div><div class="font-bold">{{ $preview['total_boxes'] }}</div></div><div><div class="text-xs text-slate-500">Pemasukan</div><div class="font-bold">{{ \App\Support\Money::rupiah($preview['total_income']) }}</div></div><div><div class="text-xs text-slate-500">Pengeluaran</div><div class="font-bold">{{ \App\Support\Money::rupiah($preview['total_expense']) }}</div></div><div><div class="text-xs text-slate-500">Bersih</div><div class="font-bold">{{ \App\Support\Money::rupiah($preview['net_income']) }}</div></div></div>
<div class="overflow-x-auto border rounded-xl"><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">Invoice</th><th class="p-3 text-left">Tanggal</th><th class="p-3 text-right">Gabus</th><th class="p-3 text-right">Bersih</th></tr></thead><tbody>@forelse($preview['invoices'] as $invoice)<tr class="border-t"><td class="p-3">{{ $invoice->invoice_number }}</td><td class="p-3">{{ $invoice->invoice_date->format('d/m/Y') }}</td><td class="p-3 text-right">{{ $invoice->total_boxes }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($invoice->net_income) }}</td></tr>@empty<tr><td colspan="4" class="p-6 text-center text-slate-500">Tidak ada invoice posted.</td></tr>@endforelse</tbody></table></div>
<div class="grid md:grid-cols-2 gap-4"><div><label class="block text-sm font-medium mb-1">Persentase Kapten (%)</label><input type="number" step="0.01" min="0" max="100" name="captain_percentage" value="{{ old('captain_percentage', 20) }}" required class="w-full rounded-lg border-slate-300"></div><div><label class="block text-sm font-medium mb-1">Catatan</label><input name="notes" value="{{ old('notes') }}" class="w-full rounded-lg border-slate-300"></div></div>
<button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold" @disabled($preview['total_invoices'] < 1)>Buat Grand Invoice</button>
</form>
@endif
@endsection
