@extends('layouts.app')
@section('title', 'Edit Pengeluaran Non-Operasional')
@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-bold">Edit Pengeluaran Non-Operasional</h1>
    <p class="text-sm text-slate-500">Data closed tidak bisa diedit karena sudah masuk tutup bulan.</p>
</div>
<form method="POST" action="{{ route('expenses.update', $expense) }}" class="bg-white rounded-2xl shadow p-4 space-y-4">
    @csrf @method('PUT')
    @include('owner-expenses.form')
    <button class="w-full md:w-auto px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold">Update</button>
</form>
@endsection
