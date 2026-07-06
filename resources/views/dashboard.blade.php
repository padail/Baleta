@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
    $icon = function (string $name) {
        $map = [
            'fish' => '<path d="M16 8c2.5 0 4.5 1.5 6 4-1.5 2.5-3.5 4-6 4-2.7 0-4.5-1.7-6-4 1.5-2.3 3.3-4 6-4z"/><path d="M2 12l7-4v8l-7-4z"/><circle cx="17" cy="11" r=".5"/>',
            'ship' => '<path d="M4 17h16l-2 4H6l-2-4z"/><path d="M7 17V8h7l3 5v4"/><path d="M9 11h4"/>',
            'coin' => '<circle cx="12" cy="12" r="8"/><path d="M12 8v8M9 10h4.5a2 2 0 0 1 0 4H10"/>',
            'anchor' => '<circle cx="12" cy="5" r="2"/><path d="M12 7v12M7 10h10M5 15a7 7 0 0 0 14 0"/>',
            'arrow' => '<path d="M5 12h14M13 5l7 7-7 7"/>',
        ];
        return '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'.($map[$name] ?? $map['fish']).'</svg>';
    };
@endphp

<section class="mb-5">
    <div class="ocean-panel rounded-[2rem] p-5 md:p-7 text-white shadow-xl shadow-teal-950/10 overflow-hidden relative">
        <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-teal-300/15"></div>
        <div class="absolute right-10 bottom-0 h-20 w-40 rounded-t-full bg-amber-200/10"></div>
        <div class="absolute -left-10 -bottom-16 h-44 w-44 rounded-full bg-cyan-200/10"></div>
        <div class="relative">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/10 px-3 py-1.5 text-xs font-bold text-teal-50">
                {!! $icon('fish') !!}
                Ringkasan bulan ini
            </div>
            <h1 class="text-2xl md:text-4xl font-black mt-4 leading-tight">Halo, {{ auth()->user()->name }}</h1>
            <p class="text-sm text-teal-50/80 mt-2 max-w-2xl">Pantau hasil ikan, invoice harian, rekap bulanan per kapal, dan catatan non-operasional dari layar HP.</p>
            <div class="grid grid-cols-2 md:flex gap-2 mt-5">
                <a href="{{ route('invoices.create') }}" class="rounded-2xl bg-teal-500 px-4 py-3 text-center text-sm font-black shadow-lg shadow-teal-900/20 flex items-center justify-center gap-2">{!! $icon('fish') !!} Invoice Baru</a>
                <a href="{{ route('monthly-closings.create') }}" class="rounded-2xl bg-amber-100 text-[#073b3a] px-4 py-3 text-center text-sm font-black flex items-center justify-center gap-2">{!! $icon('anchor') !!} Tutup Bulan</a>
            </div>
        </div>
    </div>
</section>

<section class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-teal-900/5"><div class="flex items-center justify-between gap-2"><div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Pemasukan Kotor</div><span class="text-teal-700">{!! $icon('fish') !!}</span></div><div class="mt-2 text-lg md:text-2xl font-black">{{ \App\Support\Money::rupiah($summary['total_income']) }}</div></div>
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-teal-900/5"><div class="flex items-center justify-between gap-2"><div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Ongkir + Gabus</div><span class="text-amber-700">{!! $icon('ship') !!}</span></div><div class="mt-2 text-lg md:text-2xl font-black text-amber-700">{{ \App\Support\Money::rupiah($summary['total_expense']) }}</div></div>
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-teal-900/5"><div class="flex items-center justify-between gap-2"><div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Bersih Harian</div><span class="text-teal-700">{!! $icon('coin') !!}</span></div><div class="mt-2 text-lg md:text-2xl font-black text-teal-700">{{ \App\Support\Money::rupiah($summary['net_income']) }}</div></div>
    <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-teal-900/5"><div class="flex items-center justify-between gap-2"><div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Kapal Aktif</div><span class="text-teal-700">{!! $icon('ship') !!}</span></div><div class="mt-2 text-lg md:text-2xl font-black">{{ $summary['active_ships'] }}</div></div>
</section>

<section class="grid md:grid-cols-3 gap-4 mb-5">
    <a href="{{ route('ships.index') }}" class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-teal-900/5 flex items-center justify-between hover:border-teal-200 transition"><div class="flex items-center gap-3"><span class="h-11 w-11 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center">{!! $icon('ship') !!}</span><div><div class="font-black">Kapal</div><div class="text-sm text-slate-500">Kelola armada dan kapten</div></div></div><span class="text-teal-700">{!! $icon('arrow') !!}</span></a>
    <a href="{{ route('expenses.index') }}" class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-teal-900/5 flex items-center justify-between hover:border-teal-200 transition"><div class="flex items-center gap-3"><span class="h-11 w-11 rounded-2xl bg-rose-50 text-rose-700 flex items-center justify-center">{!! $icon('coin') !!}</span><div><div class="font-black">Non-Operasional</div><div class="text-sm text-slate-500">Rekap berdiri sendiri</div></div></div><span class="text-teal-700">{!! $icon('arrow') !!}</span></a>
    @if(auth()->user()?->isOwner())
        <a href="{{ route('admins.index') }}" class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-teal-900/5 flex items-center justify-between hover:border-teal-200 transition"><div class="flex items-center gap-3"><span class="h-11 w-11 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center">{!! $icon('anchor') !!}</span><div><div class="font-black">Admin</div><div class="text-sm text-slate-500">Akun input data</div></div></div><span class="text-teal-700">{!! $icon('arrow') !!}</span></a>
    @else
        <a href="{{ route('captains.index') }}" class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-teal-900/5 flex items-center justify-between hover:border-teal-200 transition"><div class="flex items-center gap-3"><span class="h-11 w-11 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center">{!! $icon('anchor') !!}</span><div><div class="font-black">Kapten</div><div class="text-sm text-slate-500">Data kapten kapal</div></div></div><span class="text-teal-700">{!! $icon('arrow') !!}</span></a>
    @endif
</section>

<section class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 rounded-[1.75rem] bg-white shadow-sm border border-teal-900/5 overflow-hidden">
        <div class="p-4 md:p-5 border-b border-slate-100 flex items-center justify-between">
            <div><h2 class="font-black text-lg">Invoice Terbaru</h2><p class="text-sm text-slate-500">Data terakhir yang dicatat.</p></div>
            <a href="{{ route('invoices.index') }}" class="text-sm font-black text-teal-700">Lihat semua</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($latestInvoices as $invoice)
                <a href="{{ route('invoices.show', $invoice) }}" class="block p-4 hover:bg-teal-50/50">
                    <div class="flex justify-between gap-4">
                        <div class="min-w-0"><div class="font-black text-teal-700 truncate">{{ $invoice->invoice_number }}</div><div class="text-xs text-slate-500 mt-1">{{ $invoice->invoice_date->format('d/m/Y') }} · {{ $invoice->ship?->name }} · {{ strtoupper($invoice->status) }}</div></div>
                        <div class="text-right shrink-0"><div class="font-black">{{ \App\Support\Money::rupiah($invoice->net_income) }}</div><div class="text-[11px] text-slate-400">bersih</div></div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-slate-500">Belum ada invoice.</div>
            @endforelse
        </div>
    </div>
    <div class="rounded-[1.75rem] bg-white shadow-sm border border-teal-900/5 p-5">
        <h2 class="font-black text-lg">Ringkasan Lanjutan</h2>
        <div class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between gap-3"><span class="text-slate-500">Operasional Kapal</span><strong class="text-amber-700">{{ \App\Support\Money::rupiah($summary['operational_expense_total']) }}</strong></div>
            <div class="flex justify-between gap-3"><span class="text-slate-500">Jasa Kapten</span><strong>{{ \App\Support\Money::rupiah($summary['captain_share']) }}</strong></div>
            <div class="flex justify-between gap-3"><span class="text-slate-500">Owner dari Kapal</span><strong class="text-emerald-700">{{ \App\Support\Money::rupiah($summary['owner_share']) }}</strong></div>
            <div class="pt-3 border-t flex justify-between gap-3"><span class="text-slate-500">Non-Op Terpisah</span><strong class="text-rose-700">{{ \App\Support\Money::rupiah($summary['non_operational_expense_total']) }}</strong></div>
        </div>
    </div>
</section>
@endsection
