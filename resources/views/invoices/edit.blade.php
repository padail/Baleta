@extends('layouts.app')
@section('title', 'Edit Invoice')
@section('content')
<h1 class="text-2xl font-bold mb-5">Edit Invoice</h1>
<form method="POST" action="{{ route('invoices.update', $invoice) }}" class="bg-white rounded-xl shadow p-5 space-y-5">
@csrf @method('PUT')
@include('invoices.form', ['invoice' => $invoice])
<button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">Update Draft</button>
</form>
@endsection
