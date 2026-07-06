@if (session('success'))
    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
        <div class="font-semibold">Berhasil</div>
        <div>{{ session('success') }}</div>
    </div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
        <div class="font-semibold">Terjadi kesalahan</div>
        <div>{{ session('error') }}</div>
    </div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
        <div class="font-semibold mb-1">Periksa kembali input berikut.</div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
