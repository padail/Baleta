@extends('layouts.app')
@section('title', 'Tambah Pengeluaran Non-Operasional')
@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-bold">Tambah Pengeluaran Non-Operasional</h1>
    <p class="text-sm text-slate-500">Tidak mempengaruhi perhitungan kapal dan jasa kapten.</p>
</div>
<form method="POST" action="{{ route('expenses.store') }}" class="bg-white rounded-2xl shadow p-4 space-y-4">
    @csrf
    @include('owner-expenses.form')
    <button class="w-full md:w-auto px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold">Simpan</button>
</form>
@endsection
