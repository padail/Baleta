@extends('layouts.app')
@section('title', 'Edit Kapten')
@section('content')
<h1 class="text-2xl font-bold mb-5">Edit Kapten</h1><form method="POST" action="{{ route('captains.update', $captain) }}" class="bg-white rounded-xl shadow p-5 space-y-4 max-w-2xl">@csrf @method('PUT') @include('captains.form', ['captain' => $captain])<button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">Update</button></form>
@endsection
