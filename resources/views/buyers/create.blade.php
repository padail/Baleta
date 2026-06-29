@extends('layouts.app')
@section('title', 'Tambah Pembeli')
@section('content')
<h1 class="text-2xl font-bold mb-5">Tambah Pembeli</h1><form method="POST" action="{{ route('buyers.store') }}" class="bg-white rounded-xl shadow p-5 space-y-4 max-w-2xl">@csrf @include('buyers.form', ['buyer' => null])<button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">Simpan</button></form>
@endsection
