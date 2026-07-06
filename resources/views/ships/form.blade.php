@php
    $activeCaptain = $ship?->activeCaptainAssignment?->captain ?? null;
@endphp
<div>
    <label class="block text-sm font-bold mb-2">Kode Kapal</label>
    <input name="code" value="{{ old('code', $ship->code ?? '') }}" placeholder="Contoh: KPL-001" class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base focus:border-teal-500 focus:ring-teal-500">
</div>
<div>
    <label class="block text-sm font-bold mb-2">Nama Kapal</label>
    <input name="name" value="{{ old('name', $ship->name ?? '') }}" required class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base focus:border-teal-500 focus:ring-teal-500" placeholder="Contoh: KM Laut Jaya">
</div>
<div class="rounded-[1.5rem] bg-teal-50 border border-teal-100 p-4 space-y-3">
    <div>
        <h2 class="font-black text-[#073b3a]">Kapten Kapal</h2>
        <p class="text-xs text-slate-500 mt-1">Masukkan nama kapten langsung di halaman ini. Baleta akan menyimpan data kapten otomatis.</p>
    </div>
    <div>
        <label class="block text-sm font-bold mb-2">Nama Kapten</label>
        <input name="captain_name" value="{{ old('captain_name', $activeCaptain->name ?? '') }}" required class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-white px-4 text-base focus:border-teal-500 focus:ring-teal-500" placeholder="Contoh: Pak Rahman">
    </div>
    <div>
        <label class="block text-sm font-bold mb-2">Nomor HP Kapten <span class="text-slate-400 font-normal">opsional</span></label>
        <input name="captain_phone" value="{{ old('captain_phone', $activeCaptain->phone ?? '') }}" class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-white px-4 text-base focus:border-teal-500 focus:ring-teal-500" placeholder="08xxxxxxxxxx">
    </div>
    <div>
        <label class="block text-sm font-bold mb-2">Tanggal Mulai Kapten</label>
        <input type="date" name="captain_start_date" value="{{ old('captain_start_date', $ship?->activeCaptainAssignment?->start_date ?? now()->toDateString()) }}" class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-white px-4 text-base focus:border-teal-500 focus:ring-teal-500">
    </div>
</div>
<div>
    <label class="block text-sm font-bold mb-2">Catatan Kapal</label>
    <textarea name="description" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-base focus:border-teal-500 focus:ring-teal-500 min-h-28" placeholder="Catatan kapal">{{ old('description', $ship->description ?? '') }}</textarea>
</div>
<label class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $ship->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300"> Kapal aktif</label>
