@extends('layouts.app')
@section('title', 'Edit Non-Operasional')
@section('content')
<div class="mb-5"><a href="{{ route('expenses.index') }}" class="inline-flex text-sm font-semibold text-slate-500 mb-3">← Rekap non-operasional</a><h1 class="text-2xl md:text-3xl font-black">Edit Non-Operasional</h1><p class="text-sm text-slate-500 mt-1">Perbarui catatan pengeluaran owner.</p></div>
<form method="POST" action="{{ route('expenses.update', $expense) }}" class="rounded-[1.75rem] bg-white p-4 md:p-6 shadow-sm border border-slate-100 space-y-4 max-w-2xl">@csrf @method('PUT') @include('owner-expenses.form')<button class="w-full md:w-auto min-h-[52px] rounded-2xl bg-teal-600 px-6 text-white font-black">Update</button></form>
@endsection
