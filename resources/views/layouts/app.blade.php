<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Aplikasi Nelayan')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#073b3a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="description" content="Aplikasi pencatatan invoice harian, rekap kapal, tutup bulan, dan pengeluaran nelayan.">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/pwa-192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/pwa-180.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Nelayan">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --sea-950: #073b3a;
            --sea-900: #0b4d4b;
            --sea-700: #0f766e;
            --sea-600: #0d9488;
            --sea-100: #ccfbf1;
            --sand-50: #fbf7ef;
            --sand-100: #f3ead7;
            --coral-600: #e0523f;
        }
        [x-cloak] { display: none !important; }
        .safe-bottom { padding-bottom: max(0.85rem, env(safe-area-inset-bottom)); }
        .safe-top { padding-top: max(0.85rem, env(safe-area-inset-top)); }
        .app-scrollbar::-webkit-scrollbar { display: none; }
        .app-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        input, select, textarea { font-size: 16px; }
        body { background: linear-gradient(180deg, #f7fbf8 0%, #f5f0e4 100%); }
        .ocean-panel {
            background:
                radial-gradient(circle at 15% 10%, rgba(94, 234, 212, .22), transparent 26%),
                radial-gradient(circle at 90% 0%, rgba(251, 191, 36, .12), transparent 24%),
                linear-gradient(135deg, #052f2f 0%, #073b3a 45%, #0f766e 100%);
        }
        .sea-glass { background: rgba(255,255,255,.78); backdrop-filter: blur(18px); }
        .snap-card { scroll-snap-align: start; }
    </style>
</head>
@php
    $navItems = [
        ['label' => 'Home', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard'), 'icon' => 'home'],
        ['label' => 'Invoice', 'route' => 'invoices.index', 'active' => request()->routeIs('invoices.*') && !request()->routeIs('invoices.create'), 'icon' => 'receipt'],
        ['label' => 'Tutup', 'route' => 'monthly-closings.index', 'active' => request()->routeIs('monthly-closings.*'), 'icon' => 'calendar'],
        ['label' => 'Non-Op', 'route' => 'expenses.index', 'active' => request()->routeIs('expenses.*'), 'icon' => 'wallet'],
    ];
    $quickItems = [
        ['label' => 'Kapal', 'route' => 'ships.index', 'active' => request()->routeIs('ships.*'), 'icon' => 'ship', 'hint' => 'Armada'],
        ['label' => 'Kapten', 'route' => 'captains.index', 'active' => request()->routeIs('captains.*'), 'icon' => 'anchor', 'hint' => 'Awak'],
        ['label' => 'Invoice Baru', 'route' => 'invoices.create', 'active' => request()->routeIs('invoices.create'), 'icon' => 'plus', 'hint' => 'Catat'],
        ['label' => 'Tutup Bulan', 'route' => 'monthly-closings.create', 'active' => request()->routeIs('monthly-closings.create'), 'icon' => 'calendar', 'hint' => 'Rekap'],
        ['label' => 'Non-Op Baru', 'route' => 'expenses.create', 'active' => request()->routeIs('expenses.create'), 'icon' => 'wallet', 'hint' => 'Biaya'],
    ];
    $svgIcon = function (string $name, string $class = 'h-5 w-5') {
        $icons = [
            'home' => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h5v-5h4v5h5v-9.5"/>',
            'receipt' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z"/><path d="M9 8h6M9 12h6M9 16h4"/>',
            'calendar' => '<path d="M7 3v4M17 3v4M4 9h16"/><rect x="4" y="5" width="16" height="16" rx="3"/><path d="M8 13h3M13 13h3M8 17h3"/>',
            'wallet' => '<path d="M4 7h15a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a3 3 0 0 1 3-3h12"/><path d="M16 13h5v4h-5a2 2 0 0 1 0-4z"/>',
            'ship' => '<path d="M4 17h16l-2 4H6l-2-4z"/><path d="M7 17V8h7l3 5v4"/><path d="M9 11h4M2 21c2 1 4 1 6 0s4-1 6 0 4 1 8 0"/>',
            'anchor' => '<circle cx="12" cy="5" r="2"/><path d="M12 7v12M7 10h10M5 15a7 7 0 0 0 14 0M5 15l-2 2M19 15l2 2"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'logout' => '<path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 3v18"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        ];
        return '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($icons[$name] ?? $icons['home']).'</svg>';
    };
@endphp
<body class="min-h-screen text-slate-900 antialiased pb-24 md:pb-0">
    <div class="min-h-screen md:grid md:grid-cols-[286px_minmax(0,1fr)]">
        <aside class="hidden md:flex md:flex-col ocean-panel text-white min-h-screen sticky top-0 shadow-2xl shadow-teal-950/20">
            <div class="p-6 border-b border-white/10">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-[1.35rem] bg-white/15 ring-1 ring-white/20 flex items-center justify-center text-teal-50 shrink-0">
                        {!! $svgIcon('ship', 'h-7 w-7') !!}
                    </div>
                    <div>
                        <div class="font-black text-lg leading-tight">Nelayan App</div>
                        <div class="text-xs text-teal-100/85">Keuangan kapal nelayan</div>
                    </div>
                </a>
            </div>
            <nav class="flex-1 p-4 space-y-1 text-sm">
                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 transition {{ $item['active'] ? 'bg-white text-[#073b3a] font-black shadow-lg shadow-black/10' : 'text-teal-50/90 hover:bg-white/10' }}">
                        <span class="{{ $item['active'] ? 'text-teal-700' : 'text-teal-100' }}">{!! $svgIcon($item['icon']) !!}</span>
                        <span>{{ $item['label'] === 'Home' ? 'Dashboard' : ($item['label'] === 'Tutup' ? 'Tutup Bulan' : ($item['label'] === 'Non-Op' ? 'Non-Operasional' : 'Invoice Harian')) }}</span>
                    </a>
                @endforeach
                <div class="pt-4 mt-4 border-t border-white/10 text-xs uppercase tracking-wider text-teal-100/60 px-4">Master Data</div>
                <a href="{{ route('ships.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 {{ request()->routeIs('ships.*') ? 'bg-white text-[#073b3a] font-black' : 'text-teal-50/90 hover:bg-white/10' }}"><span>{!! $svgIcon('ship') !!}</span>Kapal</a>
                <a href="{{ route('captains.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 {{ request()->routeIs('captains.*') ? 'bg-white text-[#073b3a] font-black' : 'text-teal-50/90 hover:bg-white/10' }}"><span>{!! $svgIcon('anchor') !!}</span>Kapten</a>
                @if(auth()->user()?->isOwner())
                    <a href="{{ route('admins.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 {{ request()->routeIs('admins.*') ? 'bg-white text-[#073b3a] font-black' : 'text-teal-50/90 hover:bg-white/10' }}"><span>{!! $svgIcon('user') !!}</span>Admin</a>
                @endif
            </nav>
            <div class="p-4 border-t border-white/10">
                <div class="rounded-[1.35rem] bg-white/10 ring-1 ring-white/10 p-4 mb-3">
                    <div class="font-bold truncate">{{ auth()->user()->name ?? '' }}</div>
                    <div class="text-xs text-teal-100/80 capitalize">{{ auth()->user()->role ?? '' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full rounded-2xl bg-[#e0523f] text-white px-4 py-3 font-bold hover:bg-[#cb4635] flex items-center justify-center gap-2">{!! $svgIcon('logout', 'h-4 w-4') !!} Keluar</button>
                </form>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="md:hidden sticky top-0 z-40 ocean-panel text-white shadow-xl shadow-teal-950/15">
                <div class="px-4 pb-3 safe-top">
                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
                            <div class="h-11 w-11 rounded-[1.25rem] bg-white/15 ring-1 ring-white/20 flex items-center justify-center text-teal-50 shrink-0">
                                {!! $svgIcon('ship', 'h-6 w-6') !!}
                            </div>
                            <div class="min-w-0">
                                <div class="font-black leading-tight truncate">Nelayan App</div>
                                <div class="text-[11px] text-teal-100/85 truncate">{{ auth()->user()->name ?? '' }} · {{ auth()->user()->role ?? '' }}</div>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                            @csrf
                            <button class="rounded-full bg-white/12 ring-1 ring-white/10 px-3 py-2 text-xs font-bold flex items-center gap-1.5">{!! $svgIcon('logout', 'h-3.5 w-3.5') !!} Keluar</button>
                        </form>
                    </div>
                </div>
                <div class="bg-white/10 border-t border-white/10">
                    <div class="overflow-x-auto app-scrollbar px-4 py-3 scroll-px-4 snap-x">
                        <div class="flex gap-2 min-w-max">
                            @foreach($quickItems as $item)
                                <a href="{{ route($item['route']) }}" class="snap-card min-w-[108px] rounded-[1.15rem] px-3 py-2.5 text-left transition {{ $item['active'] ? 'bg-white text-[#073b3a] shadow-lg shadow-black/10' : 'bg-white/12 text-white ring-1 ring-white/10' }}">
                                    <div class="flex items-center gap-2">
                                        <span class="h-8 w-8 rounded-xl flex items-center justify-center {{ $item['active'] ? 'bg-teal-50 text-teal-700' : 'bg-white/10 text-teal-50' }}">{!! $svgIcon($item['icon'], 'h-4 w-4') !!}</span>
                                        <span class="min-w-0">
                                            <span class="block text-[11px] opacity-70 leading-none">{{ $item['hint'] }}</span>
                                            <span class="block text-xs font-black leading-tight truncate">{{ $item['label'] }}</span>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                            @if(auth()->user()?->isOwner())
                                <a href="{{ route('admins.index') }}" class="snap-card min-w-[108px] rounded-[1.15rem] px-3 py-2.5 {{ request()->routeIs('admins.*') ? 'bg-white text-[#073b3a] shadow-lg shadow-black/10' : 'bg-white/12 text-white ring-1 ring-white/10' }}">
                                    <div class="flex items-center gap-2"><span class="h-8 w-8 rounded-xl flex items-center justify-center {{ request()->routeIs('admins.*') ? 'bg-teal-50 text-teal-700' : 'bg-white/10 text-teal-50' }}">{!! $svgIcon('user', 'h-4 w-4') !!}</span><span><span class="block text-[11px] opacity-70 leading-none">Akses</span><span class="block text-xs font-black leading-tight">Admin</span></span></div>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <main class="max-w-7xl mx-auto px-4 py-4 md:px-8 md:py-8">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 sea-glass border-t border-teal-900/10 safe-bottom px-3 pt-2 shadow-2xl shadow-teal-950/20">
        <div class="grid grid-cols-5 gap-1 text-[10px] text-center">
            @foreach($navItems as $item)
                @if($loop->index === 2)
                    <a href="{{ route('invoices.create') }}" class="-mt-7 flex flex-col items-center text-teal-800 font-black">
                        <div class="h-14 w-14 rounded-[1.45rem] bg-teal-600 text-white shadow-xl shadow-teal-700/25 flex items-center justify-center ring-4 ring-white">{!! $svgIcon('plus', 'h-7 w-7') !!}</div>
                        <span class="mt-1">Baru</span>
                    </a>
                @endif
                <a href="{{ route($item['route']) }}" class="rounded-2xl py-2 px-1 transition {{ $item['active'] ? 'bg-[#073b3a] text-white shadow-sm' : 'text-slate-500' }}">
                    <div class="mx-auto mb-1 flex h-5 w-5 items-center justify-center">{!! $svgIcon($item['icon'], 'h-5 w-5') !!}</div>{{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <div data-pwa-install-card hidden class="fixed inset-x-4 bottom-24 z-[60] md:left-auto md:right-6 md:bottom-6 md:w-[360px] rounded-[1.5rem] bg-[#fffdfa] border border-teal-900/10 shadow-2xl shadow-teal-950/25 p-4">
        <div class="flex gap-3">
            <div class="h-12 w-12 rounded-2xl bg-[#073b3a] flex items-center justify-center shrink-0">
                <img src="{{ asset('icons/pwa-192.png') }}" alt="Nelayan App" class="h-9 w-9 rounded-xl">
            </div>
            <div class="min-w-0 flex-1">
                <div class="font-black text-slate-900 leading-tight">Pasang Nelayan App</div>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Buka dari layar utama HP seperti aplikasi biasa.</p>
                <div data-pwa-ios-guide hidden class="mt-2 rounded-2xl bg-teal-50 text-teal-900 text-xs p-3 leading-relaxed">
                    Di iPhone, tekan tombol <b>Bagikan</b>, lalu pilih <b>Tambahkan ke Layar Utama</b>.
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button type="button" data-pwa-dismiss class="min-h-[42px] rounded-2xl bg-slate-100 text-slate-600 text-sm font-bold">Nanti</button>
                    <button type="button" data-pwa-install-button class="min-h-[42px] rounded-2xl bg-teal-700 text-white text-sm font-black shadow-lg shadow-teal-700/20">Pasang</button>
                </div>
            </div>
        </div>
    </div>

<script src="{{ asset('pwa-register.js') }}" defer></script>

<script>
(function () {
    document.addEventListener('submit', function (event) {
        if (event.defaultPrevented) return;
        const form = event.target;
        if (!form || form.dataset.allowMultiSubmit === 'true') return;
        if (form.dataset.submitted === 'true') {
            event.preventDefault();
            return false;
        }
        form.dataset.submitted = 'true';
        form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach(function (button) {
            if (button.dataset.noDisable === 'true') return;
            button.dataset.originalText = button.innerHTML || button.value || '';
            button.disabled = true;
            if (button.tagName === 'INPUT') button.value = 'Memproses...';
            else button.innerHTML = 'Memproses...';
            button.classList.add('opacity-70', 'cursor-not-allowed');
        });
    }, true);
})();
</script>

</body>
</html>
