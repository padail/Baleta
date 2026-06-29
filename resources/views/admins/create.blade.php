@extends('layouts.app')
@section('title', 'Tambah Admin')
@section('content')
<h1 class="text-2xl font-bold mb-5">Tambah Admin</h1>
<form method="POST" action="{{ route('admins.store') }}" class="bg-white rounded-xl shadow p-5 space-y-4 max-w-2xl">
@csrf
@include('admins.form', ['admin' => null])
<button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">Simpan</button>
</form>
@endsection
