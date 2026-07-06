@extends('layouts.app')
@section('title', 'Tambah Invoice')
@section('content')
<div class="mb-5"><a href="javascript:history.back()" class="inline-flex text-sm font-semibold text-slate-500 mb-3">← Kembali</a><h1 class="text-2xl md:text-3xl font-black">Tambah Invoice Harian</h1><p class="text-sm text-slate-500 mt-1">Catat pengiriman ikan, pembeli, ongkir, dan biaya angkat gabus.</p></div>
<form method="POST" action="{{ route('invoices.store') }}" class="space-y-5">
@csrf
@include('invoices.form', ['invoice' => null])
<div class="sticky bottom-24 md:static z-20 rounded-[1.5rem] bg-white/95 backdrop-blur border border-slate-100 shadow-lg p-3"><button class="w-full md:w-auto min-h-[54px] rounded-2xl bg-teal-600 text-white px-6 font-black shadow-lg shadow-teal-700/20">Simpan Draft</button></div>
</form>
@endsection
