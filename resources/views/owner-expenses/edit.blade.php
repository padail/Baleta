@extends('layouts.app')
@section('title', 'Edit Pengeluaran Rekap')
@section('content')
<h1 class="text-2xl font-bold mb-5">Edit Pengeluaran Rekap</h1>
<form method="POST" action="{{ route('expenses.update', $expense) }}" class="bg-white rounded-xl shadow p-5 space-y-4 max-w-3xl">
    @csrf @method('PUT')
    @include('owner-expenses.form')
    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold">Simpan Perubahan</button>
    <a href="{{ route('expenses.index') }}" class="px-4 py-2 bg-slate-100 rounded-lg">Kembali</a>
</form>
@endsection
