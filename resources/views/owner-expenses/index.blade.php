@extends('layouts.app')
@section('title', 'Pengeluaran Non-Operasional')
@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">Pengeluaran Non-Operasional</h1>
        <p class="text-sm text-slate-500">Pengeluaran owner yang tidak mempengaruhi pendapatan kapal dan jasa kapten.</p>
    </div>
    <a href="{{ route('expenses.create') }}" class="px-4 py-3 bg-blue-600 text-white rounded-xl text-sm font-semibold text-center">Tambah Non-Op</a>
</div>

<form method="GET" class="bg-white rounded-2xl shadow p-4 mb-5 grid grid-cols-2 md:grid-cols-5 gap-3">
    <select name="status" class="rounded-xl border-slate-300">
        <option value="">Semua Status</option>
        <option value="posted" @selected(request('status') === 'posted')>Posted</option>
        <option value="closed" @selected(request('status') === 'closed')>Closed</option>
        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
    </select>
    <input type="number" name="month" min="1" max="12" value="{{ request('month') }}" class="rounded-xl border-slate-300" placeholder="Bulan">
    <input type="number" name="year" value="{{ request('year') }}" class="rounded-xl border-slate-300" placeholder="Tahun">
    <button class="rounded-xl bg-slate-900 text-white px-4 py-2 col-span-2 md:col-span-1">Filter</button>
</form>

<div class="bg-white rounded-2xl shadow p-4 mb-5">
    <div class="text-xs text-slate-500">Total Non-Operasional</div>
    <div class="text-2xl font-bold text-red-700">{{ \App\Support\Money::rupiah((int) $summary) }}</div>
    <p class="text-xs text-slate-500 mt-1">Dikurangkan hanya pada rekap final owner, bukan pada perhitungan kapal.</p>
</div>

<div class="space-y-3 md:hidden">
@forelse($expenses as $expense)
    <div class="bg-white rounded-2xl shadow p-4">
        <div class="flex justify-between gap-3">
            <div>
                <div class="font-bold">{{ $expense->description }}</div>
                <div class="text-xs text-slate-500">{{ $expense->expense_date->format('d/m/Y') }}</div>
            </div>
            <div class="text-right">
                <div class="font-bold text-red-700">{{ \App\Support\Money::rupiah($expense->amount) }}</div>
                <div class="text-xs text-slate-500">{{ strtoupper($expense->status) }}</div>
            </div>
        </div>
        @if($expense->status === \App\Models\OwnerExpense::STATUS_POSTED)
            <div class="flex gap-3 mt-3 text-sm">
                <a href="{{ route('expenses.edit', $expense) }}" class="px-3 py-2 rounded-lg bg-blue-50 text-blue-700 font-semibold">Edit</a>
                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Batalkan pengeluaran ini?')">
                    @csrf @method('DELETE')
                    <button class="px-3 py-2 rounded-lg bg-red-50 text-red-700 font-semibold">Batalkan</button>
                </form>
            </div>
        @endif
    </div>
@empty
    <div class="bg-white rounded-2xl shadow p-6 text-center text-slate-500">Belum ada pengeluaran non-operasional.</div>
@endforelse
</div>

<div class="hidden md:block bg-white rounded-2xl shadow overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr><th class="p-3 text-left">Tanggal</th><th class="p-3 text-left">Keterangan</th><th class="p-3 text-right">Nominal</th><th class="p-3 text-left">Status</th><th class="p-3 text-right">Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                <tr class="border-t">
                    <td class="p-3">{{ $expense->expense_date->format('d/m/Y') }}</td>
                    <td class="p-3 font-medium">{{ $expense->description }}</td>
                    <td class="p-3 text-right">{{ \App\Support\Money::rupiah($expense->amount) }}</td>
                    <td class="p-3"><span class="px-2 py-1 rounded bg-slate-100 text-xs">{{ strtoupper($expense->status) }}</span></td>
                    <td class="p-3 text-right space-x-2">
                        @if($expense->status === \App\Models\OwnerExpense::STATUS_POSTED)
                            <a href="{{ route('expenses.edit', $expense) }}" class="text-blue-600">Edit</a>
                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline" onsubmit="return confirm('Batalkan pengeluaran ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600">Batalkan</button>
                            </form>
                        @else
                            <span class="text-slate-400">Terkunci</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-8 text-center text-slate-500">Belum ada pengeluaran non-operasional.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $expenses->links() }}</div>
@endsection
