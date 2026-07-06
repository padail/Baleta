@extends('layouts.app')
@section('title', 'Buat Tutup Bulan')
@section('content')
<div class="mb-5">
    <a href="{{ route('monthly-closings.index') }}" class="inline-flex text-sm font-semibold text-slate-500 mb-3">← Daftar tutup bulan</a>
    <h1 class="text-2xl md:text-3xl font-black">Buat Tutup Bulan</h1>
    <p class="text-sm text-slate-500 mt-1">Baleta akan mengambil semua invoice yang sudah diposting yang belum masuk tutup bulan.</p>
</div>

@include('monthly-closings.form', [
    'action' => route('monthly-closings.store'),
    'method' => 'POST',
    'submitLabel' => 'Simpan Tutup Bulan',
])
@endsection
