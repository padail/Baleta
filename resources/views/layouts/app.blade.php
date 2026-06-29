<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Aplikasi Nelayan')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 pb-20 md:pb-0">
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <a href="{{ route('dashboard') }}" class="font-bold text-lg text-slate-900 block truncate">Aplikasi Nelayan</a>
                    <div class="text-xs text-slate-500 truncate">{{ auth()->user()->name ?? '' }} · {{ auth()->user()->role ?? '' }}</div>
                </div>
                <div class="hidden md:flex flex-wrap items-center gap-2 text-sm">
                    <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('ships.index') }}">Kapal</a>
                    <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('captains.index') }}">Kapten</a>
                    <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('invoices.index') }}">Invoice</a>
                    <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('expenses.index') }}">Non-Op</a>
                    <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('monthly-closings.index') }}">Tutup Bulan</a>
                    @if(auth()->user()?->isOwner())
                        <a class="px-3 py-2 rounded hover:bg-slate-100" href="{{ route('admins.index') }}">Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700">Logout</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="md:hidden shrink-0">
                    @csrf
                    <button class="px-3 py-2 rounded-lg bg-red-600 text-white text-xs font-semibold">Logout</button>
                </form>
            </div>
            <div class="md:hidden mt-3 overflow-x-auto -mx-4 px-4 pb-1">
                <div class="flex gap-2 text-xs min-w-max">
                    <a class="px-3 py-2 rounded-full bg-slate-100" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="px-3 py-2 rounded-full bg-slate-100" href="{{ route('invoices.index') }}">Invoice</a>
                    <a class="px-3 py-2 rounded-full bg-slate-100" href="{{ route('monthly-closings.index') }}">Tutup Bulan</a>
                    <a class="px-3 py-2 rounded-full bg-slate-100" href="{{ route('ships.index') }}">Kapal</a>
                    <a class="px-3 py-2 rounded-full bg-slate-100" href="{{ route('captains.index') }}">Kapten</a>
                    <a class="px-3 py-2 rounded-full bg-slate-100" href="{{ route('expenses.index') }}">Non-Op</a>
                    @if(auth()->user()?->isOwner())
                        <a class="px-3 py-2 rounded-full bg-slate-100" href="{{ route('admins.index') }}">Admin</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-5 md:py-6">
        @include('partials.flash')
        @yield('content')
    </main>

    <div class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t border-slate-200 px-3 py-2">
        <div class="grid grid-cols-4 gap-2 text-[11px] text-center">
            <a href="{{ route('dashboard') }}" class="rounded-xl py-2 bg-slate-100 font-semibold">Home</a>
            <a href="{{ route('invoices.create') }}" class="rounded-xl py-2 bg-blue-600 text-white font-semibold">Invoice</a>
            <a href="{{ route('monthly-closings.create') }}" class="rounded-xl py-2 bg-slate-900 text-white font-semibold">Tutup</a>
            <a href="{{ route('expenses.create') }}" class="rounded-xl py-2 bg-slate-100 font-semibold">Non-Op</a>
        </div>
    </div>
</body>
</html>
