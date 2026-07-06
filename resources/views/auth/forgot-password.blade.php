<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#073b3a">
    <meta name="description" content="Aplikasi pencatatan invoice harian, rekap kapal, tutup bulan, dan pengeluaran nelayan.">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/pwa-192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/pwa-180.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Nelayan">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>body{background:linear-gradient(135deg,#052f2f,#073b3a 55%,#0f766e)}</style>
</head>
<body class="min-h-screen flex items-end md:items-center justify-center px-4 py-6">
    <div class="w-full max-w-md bg-[#fffdfa] rounded-[2rem] shadow-2xl p-6 border border-white/70">
        <h1 class="text-2xl font-black text-slate-900">Lupa Password</h1>
        <p class="text-sm text-slate-500 mt-2 mb-5">Masukkan email akun. Sistem akan mengirim tautan reset password.</p>
        @if(session('status'))<div class="mb-4 rounded-2xl bg-teal-50 text-teal-700 px-4 py-3 text-sm">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="mb-4 rounded-2xl bg-rose-50 text-rose-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <input type="email" name="email" required autofocus value="{{ old('email') }}" class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-stone-50 px-4 text-base focus:border-teal-600 focus:ring-teal-600" placeholder="Email">
            <button class="w-full rounded-2xl bg-teal-600 text-white py-3 font-black">Kirim Link Reset</button>
        </form>
        <a href="{{ route('login') }}" class="block mt-5 text-center text-sm font-bold text-teal-700">Kembali login</a>
    </div>
    <script src="{{ asset('pwa-register.js') }}" defer></script>
</body>
</html>
