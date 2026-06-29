@extends('layouts.app')
@section('title', 'Tutup Bulan')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">Tutup Bulan</h1>
        <p class="text-sm text-slate-500">Rekap final owner yang berasal dari rekap bulanan setiap kapal.</p>
    </div>
    <a href="{{ route('monthly-closings.create') }}" class="px-4 py-3 bg-blue-600 text-white rounded-xl text-sm font-semibold text-center">Buat Tutup Bulan</a>
</div>

<form method="GET" class="bg-white rounded-2xl shadow p-4 mb-5 grid grid-cols-2 md:grid-cols-5 gap-3">
    <input type="number" name="month" min="1" max="12" value="{{ request('month') }}" class="rounded-xl border-slate-300" placeholder="Bulan">
    <input type="number" name="year" value="{{ request('year') }}" class="rounded-xl border-slate-300" placeholder="Tahun">
    <button class="rounded-xl bg-slate-900 text-white px-4 py-2 col-span-2 md:col-span-1">Filter</button>
</form>

<div class="space-y-3 md:hidden">
@forelse($closings as $closing)
    <a href="{{ route('monthly-closings.show', $closing) }}" class="block bg-white rounded-2xl shadow p-4">
        <div class="flex justify-between gap-3"><div class="font-bold">{{ $closing->closing_number }}</div><span class="text-xs px-2 py-1 rounded bg-slate-100">{{ strtoupper($closing->status) }}</span></div>
        <div class="text-sm text-slate-500 mt-1">{{ str_pad($closing->month, 2, '0', STR_PAD_LEFT) }}/{{ $closing->year }} · {{ $closing->total_ships }} kapal · {{ $closing->total_invoices }} invoice</div>
        <div class="grid grid-cols-2 gap-2 mt-3 text-sm">
            <div><div class="text-xs text-slate-500">Owner Kapal</div><div class="font-semibold">{{ \App\Support\Money::rupiah($closing->owner_share) }}</div></div>
            <div><div class="text-xs text-slate-500">Final Owner</div><div class="font-semibold text-green-700">{{ \App\Support\Money::rupiah($closing->owner_final_income) }}</div></div>
        </div>
    </a>
@empty
    <div class="bg-white rounded-2xl shadow p-6 text-center text-slate-500">Belum ada tutup bulan.</div>
@endforelse
</div>

<div class="hidden md:block bg-white rounded-2xl shadow overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50"><tr><th class="p-3 text-left">Nomor</th><th class="p-3 text-left">Periode</th><th class="p-3 text-right">Kapal</th><th class="p-3 text-right">Invoice</th><th class="p-3 text-right">Owner Kapal</th><th class="p-3 text-right">Non-Op</th><th class="p-3 text-right">Final Owner</th><th class="p-3 text-left">Status</th><th class="p-3 text-right">Aksi</th></tr></thead>
        <tbody>
        @forelse($closings as $closing)
            <tr class="border-t">
                <td class="p-3 font-semibold">{{ $closing->closing_number }}</td>
                <td class="p-3">{{ str_pad($closing->month, 2, '0', STR_PAD_LEFT) }}/{{ $closing->year }}</td>
                <td class="p-3 text-right">{{ $closing->total_ships }}</td>
                <td class="p-3 text-right">{{ $closing->total_invoices }}</td>
                <td class="p-3 text-right">{{ \App\Support\Money::rupiah($closing->owner_share) }}</td>
                <td class="p-3 text-right">{{ \App\Support\Money::rupiah($closing->non_operational_expense_total) }}</td>
                <td class="p-3 text-right font-bold text-green-700">{{ \App\Support\Money::rupiah($closing->owner_final_income) }}</td>
                <td class="p-3"><span class="px-2 py-1 rounded bg-slate-100 text-xs">{{ strtoupper($closing->status) }}</span></td>
                <td class="p-3 text-right"><a href="{{ route('monthly-closings.show', $closing) }}" class="text-blue-600 font-semibold">Detail</a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="p-8 text-center text-slate-500">Belum ada tutup bulan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $closings->links() }}</div>
@endsection
