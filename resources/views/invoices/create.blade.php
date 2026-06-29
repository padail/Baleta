@extends('layouts.app')
@section('title', 'Tambah Invoice')
@section('content')
<h1 class="text-2xl font-bold mb-5">Tambah Invoice Pengiriman</h1>
<form method="POST" action="{{ route('invoices.store') }}" class="bg-white rounded-xl shadow p-5 space-y-5">
@csrf
@include('invoices.form', ['invoice' => null])
<button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">Simpan Draft</button>
</form>
@endsection
