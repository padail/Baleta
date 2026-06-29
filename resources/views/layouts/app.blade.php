<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Aplikasi Nelayan')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-20">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('dashboard') }}" class="font-bold text-lg text-slate-900">Aplikasi Nelayan</a>
                <div class="text-xs text-slate-500">{{ auth()->user()->name ?? '' }} · {{ auth()->user()->role ?? '' }}</div>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('dashboard') }}">Dashboard</a>
                <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('ships.index') }}">Kapal</a>
                <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('captains.index') }}">Kapten</a>
                <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('invoices.index') }}">Invoice</a>
                <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('expenses.index') }}">Pengeluaran Rekap</a>
                <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('monthly-closings.index') }}">Tutup Bulan</a>
                @if(auth()->user()?->isOwner())
                    <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('admins.index') }}">Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        @include('partials.flash')
        @yield('content')
    </main>
</body>
</html>
