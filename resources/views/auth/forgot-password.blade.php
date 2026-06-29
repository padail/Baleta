<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Lupa Password</title><meta name="viewport" content="width=device-width, initial-scale=1">@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
<div class="w-full max-w-md bg-white rounded-2xl shadow p-6">
    <h1 class="text-2xl font-bold mb-2">Lupa Password</h1>
    @include('partials.flash')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <input type="email" name="email" required placeholder="Email" class="w-full rounded-lg border-slate-300">
        <button class="w-full rounded-lg bg-blue-600 text-white py-2.5 font-semibold">Kirim Link Reset</button>
    </form>
</div>
</body></html>
