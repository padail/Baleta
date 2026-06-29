@extends('layouts.app')
@section('title', 'Detail Kapal')
@section('content')
<div class="bg-white rounded-xl shadow p-5 mb-6">
    <div class="flex justify-between gap-3"><div><h1 class="text-2xl font-bold">{{ $ship->name }}</h1><p class="text-sm text-slate-500">{{ $ship->code }} · Kapten: {{ $ship->activeCaptainAssignment?->captain?->name ?? '-' }}</p></div><a href="{{ route('ships.edit', $ship) }}" class="text-blue-600 font-semibold">Edit</a></div>
    <p class="mt-4 text-slate-600">{{ $ship->description }}</p>
</div>
<div class="bg-white rounded-xl shadow overflow-x-auto"><div class="p-4 border-b font-semibold">Riwayat Invoice</div><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">Nomor</th><th class="p-3">Tanggal</th><th class="p-3 text-right">Bersih</th><th class="p-3">Status</th></tr></thead><tbody>@forelse($invoices as $invoice)<tr class="border-t"><td class="p-3"><a class="text-blue-600" href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td><td class="p-3">{{ $invoice->invoice_date->format('d/m/Y') }}</td><td class="p-3 text-right">{{ \App\Support\Money::rupiah($invoice->net_income) }}</td><td class="p-3">{{ strtoupper($invoice->status) }}</td></tr>@empty<tr><td colspan="4" class="p-6 text-center text-slate-500">Belum ada invoice.</td></tr>@endforelse</tbody></table></div><div class="mt-4">{{ $invoices->links() }}</div>
@endsection
