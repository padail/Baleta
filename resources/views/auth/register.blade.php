<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Owner - Aplikasi Nelayan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow p-6">
        <h1 class="text-2xl font-bold mb-2">Daftar Owner</h1>
        <p class="text-sm text-slate-500 mb-6">Akun publik hanya untuk bos kapal. Admin dibuat dari dashboard owner.</p>
        @include('partials.flash')
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Nama Owner</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nomor HP</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-lg border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required class="w-full rounded-lg border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required class="w-full rounded-lg border-slate-300">
            </div>
            <button class="w-full rounded-lg bg-blue-600 text-white py-2.5 font-semibold hover:bg-blue-700">Daftar</button>
        </form>
        <div class="mt-5 text-sm text-center">
            Sudah punya akun? <a class="text-blue-600 font-semibold" href="{{ route('login') }}">Login</a>
        </div>
    </div>
</body>
</html>
