@extends('layouts.app')
@section('title', 'Pengeluaran Rekap')
@section('content')
<div class="flex flex-wrap justify-between items-center gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">Pengeluaran Rekap</h1>
        <p class="text-sm text-slate-500">Catat biaya operasional bulanan dan pengeluaran non-operasional.</p>
    </div>
    <a href="{{ route('expenses.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold">Tambah Pengeluaran</a>
</div>

<form method="GET" class="bg-white rounded-xl shadow p-4 mb-5 grid md:grid-cols-5 gap-3">
    <select name="type" class="rounded-lg border-slate-300">
        <option value="">Semua Jenis</option>
        <option value="operational" @selected(request('type') === 'operational')>Operasional Bulanan</option>
        <option value="non_operational" @selected(request('type') === 'non_operational')>Non-Operasional</option>
    </select>
    <select name="status" class="rounded-lg border-slate-300">
        <option value="">Semua Status</option>
        <option value="posted" @selected(request('status') === 'posted')>Posted</option>
        <option value="closed" @selected(request('status') === 'closed')>Closed</option>
        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
    </select>
    <input type="number" name="month" min="1" max="12" value="{{ request('month') }}" class="rounded-lg border-slate-300" placeholder="Bulan">
    <input type="number" name="year" value="{{ request('year') }}" class="rounded-lg border-slate-300" placeholder="Tahun">
    <button class="rounded-lg bg-slate-800 text-white px-4 py-2">Filter</button>
</form>

<div class="grid md:grid-cols-2 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow p-4">
        <div class="text-xs text-slate-500">Total Operasional Bulanan</div>
        <div class="text-xl font-bold text-orange-700">{{ \App\Support\Money::rupiah((int) ($summary->operational_total ?? 0)) }}</div>
        <div class="text-xs text-slate-500 mt-1">Mengurangi pendapatan bersih bulanan sebelum pembagian kapten-owner.</div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="text-xs text-slate-500">Total Non-Operasional</div>
        <div class="text-xl font-bold text-red-700">{{ \App\Support\Money::rupiah((int) ($summary->non_operational_total ?? 0)) }}</div>
        <div class="text-xs text-slate-500 mt-1">Masuk rekap, tetapi tidak mempengaruhi pendapatan kapal dan jasa kapten.</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Jenis</th>
                    <th class="p-3 text-left">Kapal</th>
                    <th class="p-3 text-left">Keterangan</th>
                    <th class="p-3 text-right">Nominal</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr class="border-t">
                        <td class="p-3">{{ $expense->expense_date->format('d/m/Y') }}</td>
                        <td class="p-3">{{ $expense->typeLabel() }}</td>
                        <td class="p-3">{{ $expense->ship?->name ?? 'Umum' }}</td>
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
                    <tr><td colspan="7" class="p-8 text-center text-slate-500">Belum ada pengeluaran rekap.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $expenses->links() }}</div>
@endsection
