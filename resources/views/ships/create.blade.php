@extends('layouts.app')
@section('title', 'Tambah Kapal')
@section('content')
<div class="mb-5">
    <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 mb-3">← Kembali</a>
    <h1 class="text-2xl md:text-3xl font-black tracking-tight">Tambah Kapal</h1>
    <p class="text-sm text-slate-500 mt-1">Tambahkan kapal dan tetapkan kapten aktif.</p>
</div>
<form method="POST" action="{{ route('ships.store') }}" class="bg-white rounded-[1.75rem] shadow-sm border border-slate-100 p-4 md:p-6 space-y-4 max-w-2xl">
    @csrf 
    @php($ship = null)
    @include('ships.form')
    <button class="w-full md:w-auto min-h-[52px] rounded-2xl bg-teal-600 px-6 text-white font-bold shadow-lg shadow-teal-700/20">Simpan</button>
</form>
@endsection
