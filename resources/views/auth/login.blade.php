<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Aplikasi Nelayan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow p-6">
        <h1 class="text-2xl font-bold mb-2">Login</h1>
        <p class="text-sm text-slate-500 mb-6">Masuk untuk mencatat invoice kapal dan laporan bulanan.</p>
        @include('partials.flash')
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-lg border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required class="w-full rounded-lg border-slate-300">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300"> Ingat saya
            </label>
            <button class="w-full rounded-lg bg-blue-600 text-white py-2.5 font-semibold hover:bg-blue-700">Masuk</button>
        </form>
        <div class="mt-5 text-sm text-center">
            Belum punya akun owner? <a class="text-blue-600 font-semibold" href="{{ route('register') }}">Daftar</a>
        </div>
    </div>
</body>
</html>
