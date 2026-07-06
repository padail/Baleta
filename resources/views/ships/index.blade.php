@extends('layouts.app')
@section('title', 'Kapal')
@section('content')
<div class="mb-5 flex items-start justify-between gap-3">
    <div><h1 class="text-2xl md:text-3xl font-black">Kapal</h1><p class="text-sm text-slate-500 mt-1">Kelola kapal dan kapten aktif.</p></div>
    <a href="{{ route('ships.create') }}" class="shrink-0 rounded-2xl bg-teal-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-teal-700/20">Tambah</a>
</div>
<form method="GET" data-allow-multi-submit="true" class="mb-4 flex gap-2 rounded-[1.5rem] bg-white p-3 shadow-sm border border-slate-100">
    <input name="search" value="{{ request('search') }}" placeholder="Cari kapal" class="min-h-[48px] flex-1 rounded-2xl border-slate-200 bg-slate-50 px-4 text-base">
    <button class="rounded-2xl bg-[#073b3a] px-4 text-sm font-bold text-white">Cari</button>
</form>
<div class="space-y-3 md:hidden">
    @forelse($ships as $ship)
        <a href="{{ route('ships.show', $ship) }}" class="block rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100">
            <div class="flex justify-between gap-3"><div><div class="font-black text-lg">{{ $ship->name }}</div><div class="text-xs text-slate-500 mt-1">{{ $ship->code ?: 'Tanpa kode' }} · Kapten: {{ $ship->activeCaptainAssignment?->captain?->name ?? '-' }}</div></div><span class="rounded-full px-3 py-1 text-xs font-bold h-fit {{ $ship->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $ship->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
        </a>
    @empty
        <div class="rounded-[1.5rem] bg-white p-8 text-center text-slate-500">Belum ada kapal.</div>
    @endforelse
</div>
<div class="hidden md:block rounded-[1.5rem] bg-white shadow-sm border border-slate-100 overflow-hidden"><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-4 text-left">Kode</th><th class="p-4 text-left">Nama Kapal</th><th class="p-4 text-left">Kapten Aktif</th><th class="p-4 text-left">Keadaan</th><th class="p-4 text-right">Aksi</th></tr></thead><tbody>@forelse($ships as $ship)<tr class="border-t border-slate-100"><td class="p-4">{{ $ship->code }}</td><td class="p-4 font-bold"><a class="text-teal-700" href="{{ route('ships.show', $ship) }}">{{ $ship->name }}</a></td><td class="p-4">{{ $ship->activeCaptainAssignment?->captain?->name ?? '-' }}</td><td class="p-4">{{ $ship->is_active ? 'Aktif' : 'Nonaktif' }}</td><td class="p-4 text-right"><a class="font-bold text-teal-700" href="{{ route('ships.edit', $ship) }}">Edit</a></td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Belum ada kapal.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $ships->links() }}</div>
@endsection
