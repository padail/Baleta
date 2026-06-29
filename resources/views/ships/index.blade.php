@extends('layouts.app')
@section('title', 'Kapal')
@section('content')
<div class="flex flex-wrap justify-between items-center gap-3 mb-5"><h1 class="text-2xl font-bold">Kapal</h1><a href="{{ route('ships.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Tambah Kapal</a></div>
<form method="GET" class="mb-4"><input name="search" value="{{ request('search') }}" placeholder="Cari kapal" class="rounded-lg border-slate-300"><button class="px-4 py-2 rounded-lg bg-slate-800 text-white">Cari</button></form>
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">Kode</th><th class="p-3 text-left">Nama Kapal</th><th class="p-3 text-left">Kapten Aktif</th><th class="p-3 text-left">Status</th><th class="p-3 text-right">Aksi</th></tr></thead><tbody>
@forelse($ships as $ship)<tr class="border-t"><td class="p-3">{{ $ship->code }}</td><td class="p-3 font-semibold"><a class="text-blue-600" href="{{ route('ships.show', $ship) }}">{{ $ship->name }}</a></td><td class="p-3">{{ $ship->activeCaptainAssignment?->captain?->name ?? '-' }}</td><td class="p-3">{{ $ship->is_active ? 'Aktif' : 'Nonaktif' }}</td><td class="p-3 text-right"><a class="text-blue-600" href="{{ route('ships.edit', $ship) }}">Edit</a></td></tr>@empty<tr><td colspan="5" class="p-6 text-center text-slate-500">Belum ada kapal.</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $ships->links() }}</div>
@endsection
