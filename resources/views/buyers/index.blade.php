@extends('layouts.app')
@section('title', 'Pembeli')
@section('content')
<div class="flex flex-wrap justify-between items-center gap-3 mb-5"><h1 class="text-2xl font-bold">Pembeli</h1><a href="{{ route('buyers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Tambah Pembeli</a></div>
<form method="GET" class="mb-4"><input name="search" value="{{ request('search') }}" placeholder="Cari pembeli" class="rounded-lg border-slate-300"><button class="px-4 py-2 rounded-lg bg-slate-800 text-white">Cari</button></form>
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-left">HP</th><th class="p-3 text-left">Status</th><th class="p-3 text-right">Aksi</th></tr></thead><tbody>
@forelse($buyers as $buyer)<tr class="border-t"><td class="p-3 font-semibold">{{ $buyer->name }}</td><td class="p-3">{{ $buyer->phone }}</td><td class="p-3">{{ $buyer->is_active ? 'Aktif' : 'Nonaktif' }}</td><td class="p-3 text-right"><a class="text-blue-600" href="{{ route('buyers.edit', $buyer) }}">Edit</a></td></tr>@empty<tr><td colspan="4" class="p-6 text-center text-slate-500">Belum ada pembeli.</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $buyers->links() }}</div>
@endsection
