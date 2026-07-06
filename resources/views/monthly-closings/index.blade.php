@extends('layouts.app')
@section('title', 'Tutup Bulan')
@section('content')
<div class="mb-5 flex items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl md:text-3xl font-black">Tutup Bulan</h1>
        <p class="text-sm text-slate-500 mt-1">Rekap final owner berdasarkan urutan tutup bulan, bukan kalender.</p>
    </div>
    <a href="{{ route('monthly-closings.create') }}" class="rounded-2xl bg-teal-600 px-4 py-3 text-sm font-bold text-white">Buat</a>
</div>

<form method="GET" data-allow-multi-submit="true" class="mb-4 flex gap-2 rounded-[1.5rem] bg-white p-3 shadow-sm border border-slate-100">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nomor atau nama rekap" class="min-h-[48px] flex-1 rounded-2xl border-slate-200 bg-slate-50 px-4 text-base">
    <button class="rounded-2xl bg-[#073b3a] px-4 text-sm font-bold text-white">Cari</button>
</form>

<div class="rounded-[1.5rem] bg-teal-50 border border-teal-100 p-4 text-sm text-teal-950 mb-4">
    <strong>Catatan:</strong> Tutup bulan di Baleta memakai urutan. Contoh: Tutup Bulan 1, Tutup Bulan 2, dan seterusnya. Tanggal kalender tetap disimpan sebagai waktu pencatatan dan rentang invoice.
</div>

<div class="space-y-3 md:hidden">
    @forelse($closings as $closing)
        <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100">
            <a href="{{ route('monthly-closings.show', $closing) }}" class="block">
                <div class="flex justify-between gap-3">
                    <div>
                        <div class="font-black text-teal-700">{{ $closing->display_period }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ $closing->closing_number }} · {{ $closing->display_date_range }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500">Hasil Pemilik dari Kapal</div>
                        <div class="font-black text-green-700">{{ \App\Support\Money::rupiah($closing->owner_share) }}</div>
                    </div>
                </div>
            </a>
            <div class="mt-3 grid grid-cols-3 gap-2 text-xs font-bold">
                <a href="{{ route('monthly-closings.show', $closing) }}" class="rounded-xl bg-slate-100 px-3 py-2 text-center text-slate-700">Detail</a>
                <a href="{{ route('monthly-closings.edit', $closing) }}" class="rounded-xl bg-teal-50 px-3 py-2 text-center text-teal-700">Edit</a>
                <form method="POST" action="{{ route('monthly-closings.destroy', $closing) }}" onsubmit="return confirm('Hapus tutup bulan ini? Invoice harian akan kembali berstatus sudah diposting.')">
                    @csrf @method('DELETE')
                    <button class="w-full rounded-xl bg-rose-50 px-3 py-2 text-center text-rose-700">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="rounded-[1.5rem] bg-white p-8 text-center text-slate-500">Belum ada tutup bulan.</div>
    @endforelse
</div>

<div class="hidden md:block rounded-[1.5rem] bg-white shadow-sm border border-slate-100 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="p-4 text-left">Urutan</th>
                <th class="p-4 text-left">Nomor</th>
                <th class="p-4 text-left">Rentang Invoice</th>
                <th class="p-4 text-right">Bersih Harian</th>
                <th class="p-4 text-right">Operasional</th>
                <th class="p-4 text-right">Jasa Kapten</th>
                <th class="p-4 text-right">Hasil Pemilik dari Kapal</th>
                <th class="p-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($closings as $closing)
                <tr class="border-t border-slate-100">
                    <td class="p-4 font-black text-teal-700"><a href="{{ route('monthly-closings.show', $closing) }}">{{ $closing->display_period }}</a></td>
                    <td class="p-4">{{ $closing->closing_number }}</td>
                    <td class="p-4">{{ $closing->display_date_range }}</td>
                    <td class="p-4 text-right">{{ \App\Support\Money::rupiah($closing->daily_net_income) }}</td>
                    <td class="p-4 text-right">{{ \App\Support\Money::rupiah($closing->operational_expense_total) }}</td>
                    <td class="p-4 text-right">{{ \App\Support\Money::rupiah($closing->captain_share) }}</td>
                    <td class="p-4 text-right font-bold text-green-700">{{ \App\Support\Money::rupiah($closing->owner_share) }}</td>
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a class="font-bold text-teal-700" href="{{ route('monthly-closings.edit', $closing) }}">Edit</a>
                            <form method="POST" action="{{ route('monthly-closings.destroy', $closing) }}" onsubmit="return confirm('Hapus tutup bulan ini? Invoice harian akan kembali berstatus sudah diposting.')">
                                @csrf @method('DELETE')
                                <button class="font-bold text-rose-600">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="p-8 text-center text-slate-500">Belum ada tutup bulan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $closings->links() }}</div>
@endsection
