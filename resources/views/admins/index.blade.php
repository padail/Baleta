@extends('layouts.app')
@section('title', 'Admin')
@section('content')
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-bold">Admin</h1>
    <a href="{{ route('admins.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Tambah Admin</a>
</div>
<div class="bg-white rounded-xl shadow overflow-x-auto">
<table class="min-w-full text-sm">
<thead class="bg-slate-50"><tr><th class="p-3 text-left">Nama</th><th class="p-3 text-left">Email</th><th class="p-3 text-left">HP</th><th class="p-3 text-left">Status</th><th class="p-3 text-right">Aksi</th></tr></thead>
<tbody>
@forelse($admins as $admin)
<tr class="border-t"><td class="p-3 font-semibold">{{ $admin->name }}</td><td class="p-3">{{ $admin->email }}</td><td class="p-3">{{ $admin->phone }}</td><td class="p-3">{{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}</td><td class="p-3 text-right"><a class="text-blue-600" href="{{ route('admins.edit', $admin) }}">Edit</a></td></tr>
@empty
<tr><td colspan="5" class="p-6 text-center text-slate-500">Belum ada admin.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-4">{{ $admins->links() }}</div>
@endsection
