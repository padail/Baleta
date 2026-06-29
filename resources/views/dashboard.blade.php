@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <p class="text-sm text-slate-500">Ringkasan performa bulan ini.</p>
    </div>
    <div class="grid grid-cols-2 md:flex gap-2">
        <a href="{{ route('expenses.create') }}" class="rounded-xl bg-slate-900 text-white px-4 py-3 text-sm font-semibold text-center">Non-Op</a>
        <a href="{{ route('invoices.create') }}" class="rounded-xl bg-blue-600 text-white px-4 py-3 text-sm font-semibold text-center">Tambah Invoice</a>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Pemasukan Kotor</div><div class="text-lg md:text-xl font-bold">{{ \App\Support\Money::rupiah($summary['total_income']) }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Ongkir + Gabus</div><div class="text-lg md:text-xl font-bold">{{ \App\Support\Money::rupiah($summary['total_expense']) }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Bersih Harian</div><div class="text-lg md:text-xl font-bold text-blue-700">{{ \App\Support\Money::rupiah($summary['net_income']) }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Kapal Aktif</div><div class="text-lg md:text-xl font-bold">{{ $summary['active_ships'] }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Operasional Kapal</div><div class="text-lg md:text-xl font-bold text-orange-700">{{ \App\Support\Money::rupiah($summary['operational_expense_total']) }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Jasa Kapten</div><div class="text-lg md:text-xl font-bold">{{ \App\Support\Money::rupiah($summary['captain_share']) }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Non-Operasional</div><div class="text-lg md:text-xl font-bold text-red-700">{{ \App\Support\Money::rupiah($summary['non_operational_expense_total']) }}</div></div>
    <div class="bg-white rounded-2xl shadow p-4"><div class="text-xs text-slate-500">Final Owner</div><div class="text-lg md:text-xl font-bold text-green-700">{{ \App\Support\Money::rupiah($summary['owner_final_income']) }}</div></div>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="p-4 border-b font-semibold">Invoice Terbaru</div>
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50"><tr><th class="p-3 text-left">Nomor</th><th class="p-3 text-left">Tanggal</th><th class="p-3 text-left">Kapal</th><th class="p-3 text-right">Bersih Harian</th><th class="p-3 text-left">Status</th></tr></thead>
            <tbody>
                @forelse($latestInvoices as $invoice)
                    <tr class="border-t">
                        <td class="p-3"><a class="text-blue-600 font-semibold" href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                        <td class="p-3">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        <td class="p-3">{{ $invoice->ship?->name }}</td>
                        <td class="p-3 text-right">{{ \App\Support\Money::rupiah($invoice->net_income) }}</td>
                        <td class="p-3">{{ strtoupper($invoice->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-slate-500">Belum ada invoice.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="md:hidden divide-y">
        @forelse($latestInvoices as $invoice)
            <a href="{{ route('invoices.show', $invoice) }}" class="block p-4">
                <div class="flex justify-between gap-3"><div class="font-semibold text-blue-600">{{ $invoice->invoice_number }}</div><div class="font-semibold">{{ \App\Support\Money::rupiah($invoice->net_income) }}</div></div>
                <div class="text-xs text-slate-500 mt-1">{{ $invoice->invoice_date->format('d/m/Y') }} · {{ $invoice->ship?->name }} · {{ strtoupper($invoice->status) }}</div>
            </a>
        @empty
            <div class="p-6 text-center text-slate-500">Belum ada invoice.</div>
        @endforelse
    </div>
</div>
@endsection
