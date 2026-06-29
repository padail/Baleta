@extends('layouts.app')
@section('title', 'Tutup Bulan')
@section('content')
<div class="flex flex-wrap justify-between items-center gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">Tutup Bulan</h1>
        <p class="text-sm text-slate-500">Grand invoice bulanan seluruh kapal.</p>
    </div>
    <a href="{{ route('monthly-closings.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Buat Tutup Bulan</a>
</div>
<form method="GET" class="bg-white rounded-xl shadow p-4 mb-4 grid md:grid-cols-3 gap-3 text-sm">
    <input type="number" name="month" min="1" max="12" value="{{ request('month') }}" placeholder="Bulan" class="rounded-lg border-slate-300">
    <input type="number" name="year" value="{{ request('year', now()->year) }}" class="rounded-lg border-slate-300">
    <button class="rounded-lg bg-slate-800 text-white px-4 py-2">Filter</button>
</form>
<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50"><tr><th class="p-3 text-left">Nomor</th><th class="p-3 text-left">Periode</th><th class="p-3 text-right">Kapal</th><th class="p-3 text-right">Invoice</th><th class="p-3 text-right">Bersih Harian</th><th class="p-3 text-right">Operasional</th><th class="p-3 text-right">Dasar Bagi</th><th class="p-3 text-right">Jasa Kapten</th><th class="p-3 text-right">Owner Final</th><th class="p-3 text-left">Status</th></tr></thead>
        <tbody>@forelse($closings as $closing)<tr class="border-t"><td class="p-3"><a class="text-blue-600 font-semibold" href="{{ route('monthly-closings.show', $closing) }}">{{ $closing->closing_number }}</a></td><td class="p-3">{{ $closing->month }}/{{ $closing->year }}</td><td class="p-3 text-right">{{ $closing->total_ships }}</td><td class="p-3 text-right">{{ $closing->total_invoices }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($closing->daily_net_income ?: $closing->net_income) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($closing->operational_expense_total) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($closing->distributable_income) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($closing->captain_share) }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($closing->owner_final_income) }}</td><td class="p-3">{{ strtoupper($closing->status) }}</td></tr>@empty<tr><td colspan="10" class="p-6 text-center text-slate-500">Belum ada tutup bulan.</td></tr>@endforelse</tbody>
    </table>
</div>
<div class="mt-4">{{ $closings->links() }}</div>
@endsection
