@extends('layouts.app')
@section('title', 'Edit Tutup Bulan')
@section('content')
<div class="mb-5">
    <a href="{{ route('monthly-closings.show', $closing) }}" class="inline-flex text-sm font-semibold text-slate-500 mb-3">← Detail tutup bulan</a>
    <h1 class="text-2xl md:text-3xl font-black">Edit {{ $closing->display_period }}</h1>
    <p class="text-sm text-slate-500 mt-1">Perbarui biaya operasional kapal, persentase jasa kapten, dan catatan.</p>
</div>

@include('monthly-closings.form', [
    'action' => route('monthly-closings.update', $closing),
    'method' => 'PUT',
    'submitLabel' => 'Simpan Perubahan',
    'nextPeriodNumber' => $closing->closing_period_number,
])
@endsection
