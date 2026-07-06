<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pemilik Kapal - Baleta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#073b3a">
    <meta name="description" content="Aplikasi pencatatan invoice harian, rekap kapal, tutup bulan, dan pengeluaran nelayan.">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/pwa-192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/pwa-180.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Baleta">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: radial-gradient(circle at 20% 10%, rgba(45, 212, 191, .22), transparent 26%), radial-gradient(circle at 80% 0%, rgba(245, 158, 11, .16), transparent 22%), linear-gradient(135deg, #052f2f 0%, #073b3a 55%, #0f766e 100%); }
    </style>
</head>
<body class="min-h-screen text-slate-900">
    <main class="min-h-screen flex items-end md:items-center justify-center px-4 py-6">
        <div class="w-full max-w-md">
            <div class="text-white mb-6 px-1">
                <div class="h-14 w-14 rounded-[1.35rem] bg-white/15 ring-1 ring-white/20 flex items-center justify-center text-teal-50 mb-4">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17h16l-2 4H6l-2-4z"/><path d="M7 17V8h7l3 5v4"/><path d="M9 11h4"/></svg>
                </div>
                <h1 class="text-3xl font-black leading-tight">Daftar Pemilik Kapal</h1>
                <p class="text-teal-50/80 mt-2 text-sm">Buat akun bos kapal untuk mulai mencatat hasil ikan, kapal, kapten, dan rekap bulanan.</p>
            </div>
            <div class="bg-[#fffdfa] rounded-[2rem] shadow-2xl p-5 md:p-7 border border-white/70">
                @if ($errors->any())
                    <div class="mb-4 rounded-2xl bg-rose-50 text-rose-700 px-4 py-3 text-sm"><ul class="list-disc pl-4">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Pemilik</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-stone-50 px-4 text-base focus:border-teal-600 focus:ring-teal-600" placeholder="Nama lengkap">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nomor HP</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-stone-50 px-4 text-base focus:border-teal-600 focus:ring-teal-600" placeholder="08xxxxxxxxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Surel</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-stone-50 px-4 text-base focus:border-teal-600 focus:ring-teal-600" placeholder="email@contoh.com">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kata sandi</label>
                        <input type="password" name="password" required class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-stone-50 px-4 text-base focus:border-teal-600 focus:ring-teal-600" placeholder="Minimal 8 karakter">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Konfirmasi Kata sandi</label>
                        <input type="password" name="password_confirmation" required class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-stone-50 px-4 text-base focus:border-teal-600 focus:ring-teal-600" placeholder="Ulangi kata sandi">
                    </div>
                    <button class="w-full min-h-[54px] rounded-2xl bg-teal-600 text-white font-black text-base shadow-lg shadow-teal-700/25">Daftar</button>
                </form>
                <div class="mt-6 text-center text-sm text-slate-600">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-teal-700 font-black">Masuk</a>
                </div>
            </div>
        </div>
    </main>
    <script src="{{ asset('pwa-register.js') }}" defer></script>
</body>
</html>
