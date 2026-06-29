<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Reset Password</title><meta name="viewport" content="width=device-width, initial-scale=1">@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
<div class="w-full max-w-md bg-white rounded-2xl shadow p-6">
    <h1 class="text-2xl font-bold mb-2">Reset Password</h1>
    @include('partials.flash')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <input type="email" name="email" value="{{ old('email', $request->email) }}" required class="w-full rounded-lg border-slate-300">
        <input type="password" name="password" required placeholder="Password baru" class="w-full rounded-lg border-slate-300">
        <input type="password" name="password_confirmation" required placeholder="Konfirmasi password" class="w-full rounded-lg border-slate-300">
        <button class="w-full rounded-lg bg-blue-600 text-white py-2.5 font-semibold">Reset Password</button>
    </form>
</div>
</body></html>
