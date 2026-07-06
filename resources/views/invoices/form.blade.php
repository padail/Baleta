<div class="rounded-[1.75rem] bg-white shadow-sm border border-slate-100 p-4 md:p-6 space-y-5">
    <div class="flex items-center gap-3"><div class="h-9 w-9 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center font-black">1</div><div><h2 class="font-black">Informasi Kapal</h2><p class="text-xs text-slate-500">Pilih tanggal, kapal, dan kapal ongkir.</p></div></div>
    <div class="grid md:grid-cols-3 gap-4">
        <div><label class="block text-sm font-bold mb-2">Tanggal</label><input type="date" name="invoice_date" value="{{ old('invoice_date', $invoice?->invoice_date?->toDateString() ?? now()->toDateString()) }}" required class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base"></div>
        <div><label class="block text-sm font-bold mb-2">Kapal</label><select name="ship_id" required class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base"><option value="">Pilih kapal</option>@foreach($ships as $ship)<option value="{{ $ship->id }}" @selected(old('ship_id', $invoice->ship_id ?? '') == $ship->id)>{{ $ship->name }} · Kapten: {{ $ship->activeCaptainAssignment?->captain?->name ?? '-' }}</option>@endforeach</select></div>
        <div><label class="block text-sm font-bold mb-2">Kapal Ongkir</label><input name="carrier_boat_name" value="{{ old('carrier_boat_name', $invoice->carrier_boat_name ?? '') }}" class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base" placeholder="Nama kapal pengangkut"></div>
    </div>
</div>

<div class="rounded-[1.75rem] bg-white shadow-sm border border-slate-100 p-4 md:p-6 space-y-5">
    <div class="flex items-center gap-3"><div class="h-9 w-9 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center font-black">2</div><div><h2 class="font-black">Gabus dan Pengeluaran Harian</h2><p class="text-xs text-slate-500">Ongkir dan jasa angkat gabus dihitung di invoice harian.</p></div></div>
    <div class="grid md:grid-cols-4 gap-4">
        <div><label class="block text-sm font-bold mb-2">Total Gabus Turun</label><input type="number" min="1" name="total_boxes" id="total_boxes" value="{{ old('total_boxes', $invoice->total_boxes ?? '') }}" required class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base"></div>
        <div><label class="block text-sm font-bold mb-2">Biaya Ongkir</label><input type="number" min="0" name="shipping_cost" id="shipping_cost" value="{{ old('shipping_cost', $invoice->shipping_cost ?? 0) }}" class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base money-input"></div>
        <div><label class="block text-sm font-bold mb-2">Angkat per Gabus</label><input type="number" min="0" name="unloading_cost_per_box" id="unloading_cost_per_box" value="{{ old('unloading_cost_per_box', $invoice->unloading_cost_per_box ?? 0) }}" class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base money-input"></div>
        <div><label class="block text-sm font-bold mb-2">Biaya Tambahan</label><input type="number" min="0" name="additional_expense" id="additional_expense" value="{{ old('additional_expense', $invoice->additional_expense ?? 0) }}" class="w-full min-h-[52px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base money-input"></div>
    </div>
    <div><label class="block text-sm font-bold mb-2">Catatan</label><textarea name="notes" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-base min-h-24" placeholder="Catatan opsional">{{ old('notes', $invoice->notes ?? '') }}</textarea></div>
</div>

<div class="rounded-[1.75rem] bg-white shadow-sm border border-slate-100 overflow-hidden" id="items-wrapper">
    <div class="p-4 md:p-5 border-b border-slate-100 flex items-start justify-between gap-3">
        <div class="flex items-center gap-3"><div class="h-9 w-9 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-black">3</div><div><h2 class="font-black">Detail Pembeli</h2><p class="text-xs text-slate-500">Nama pembeli langsung diketik. Tidak perlu master data.</p></div></div>
        <button type="button" onclick="addItemRow()" class="shrink-0 rounded-2xl bg-[#073b3a] text-white px-4 py-3 text-xs font-bold">Tambah</button>
    </div>
    <div class="p-4 md:p-5 space-y-3" id="item-rows">
        @php $oldItems = old('items', $invoice?->items?->toArray() ?? [['buyer_name'=>'','fish_type'=>'','box_count'=>'','price_per_box'=>'','notes'=>'']]); @endphp
        @foreach($oldItems as $i => $item)
            <div class="item-row rounded-2xl border border-slate-100 bg-slate-50 p-3 md:p-4">
                <div class="grid md:grid-cols-12 gap-3">
                    <div class="md:col-span-4"><label class="block text-xs font-bold text-slate-500 mb-1">Nama Pembeli</label><input name="items[{{ $i }}][buyer_name]" value="{{ $item['buyer_name'] ?? ($item['buyer']['name'] ?? '') }}" required class="w-full min-h-[48px] rounded-2xl border-slate-200 bg-white px-4 text-base" placeholder="Contoh: Haji Usman"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-bold text-slate-500 mb-1">Jenis Ikan</label><input name="items[{{ $i }}][fish_type]" value="{{ $item['fish_type'] ?? '' }}" class="w-full min-h-[48px] rounded-2xl border-slate-200 bg-white px-4 text-base" placeholder="Opsional"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-bold text-slate-500 mb-1">Gabus</label><input type="number" min="1" name="items[{{ $i }}][box_count]" value="{{ $item['box_count'] ?? '' }}" oninput="recalc()" required class="box-count w-full min-h-[48px] rounded-2xl border-slate-200 bg-white px-4 text-base"></div>
                    <div class="md:col-span-3"><label class="block text-xs font-bold text-slate-500 mb-1">Harga/Gabus</label><input type="number" min="0" name="items[{{ $i }}][price_per_box]" value="{{ $item['price_per_box'] ?? '' }}" oninput="recalc()" required class="price-per-box w-full min-h-[48px] rounded-2xl border-slate-200 bg-white px-4 text-base"></div>
                    <div class="md:col-span-1 flex md:flex-col justify-between md:items-end gap-2"><div><div class="text-[11px] text-slate-400">Subtotal</div><div class="subtotal font-black whitespace-nowrap">Rp0</div></div><button type="button" onclick="removeItemRow(this)" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700">Hapus</button></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="rounded-[1.75rem] bg-[#073b3a] text-white shadow-xl p-4 md:p-5">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
        <div><div class="text-[11px] text-slate-400">Gabus Dibeli / Turun</div><div class="font-black" id="sum_boxes">0</div><div class="text-[11px] text-amber-300 mt-1" id="box_warning"></div></div>
        <div><div class="text-[11px] text-slate-400">Sisa Gabus</div><div class="font-black" id="remaining_boxes">0</div></div>
        <div><div class="text-[11px] text-slate-400">Pemasukan</div><div class="font-black" id="sum_income">Rp0</div></div>
        <div><div class="text-[11px] text-slate-400">Pengeluaran</div><div class="font-black text-amber-200" id="sum_expense">Rp0</div></div>
        <div><div class="text-[11px] text-slate-400">Bersih</div><div class="font-black text-emerald-300" id="sum_net">Rp0</div></div>
    </div>
</div>

<script>
let itemIndex = document.querySelectorAll('.item-row').length;
function formatRp(n){ return 'Rp' + Number(Math.round(n || 0)).toLocaleString('id-ID'); }
function recalc(){
    let boxes = 0, income = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const b = Number(row.querySelector('.box-count')?.value || 0);
        const p = Number(row.querySelector('.price-per-box')?.value || 0);
        const sub = b * p; boxes += b; income += sub;
        row.querySelector('.subtotal').innerText = formatRp(sub);
    });
    const totalBoxes = Number(document.getElementById('total_boxes')?.value || 0);
    const shipping = Number(document.getElementById('shipping_cost')?.value || 0);
    const unloading = Number(document.getElementById('unloading_cost_per_box')?.value || 0);
    const additional = Number(document.getElementById('additional_expense')?.value || 0);
    const expense = shipping + (totalBoxes * unloading) + additional;
    const remaining = totalBoxes - boxes;
    document.getElementById('sum_boxes').innerText = boxes + ' / ' + totalBoxes;
    document.getElementById('remaining_boxes').innerText = remaining;
    document.getElementById('box_warning').innerText = remaining < 0 ? 'Gabus dibeli melebihi gabus turun. Draft tetap bisa disimpan.' : (remaining > 0 ? 'Masih ada gabus belum tercatat pembeli.' : '');
    document.getElementById('sum_income').innerText = formatRp(income);
    document.getElementById('sum_expense').innerText = formatRp(expense);
    document.getElementById('sum_net').innerText = formatRp(income - expense);
}
function addItemRow(){
    const wrapper = document.getElementById('item-rows');
    const div = document.createElement('div'); div.className='item-row rounded-2xl border border-slate-100 bg-slate-50 p-3 md:p-4';
    div.innerHTML = `<div class="grid md:grid-cols-12 gap-3"><div class="md:col-span-4"><label class="block text-xs font-bold text-slate-500 mb-1">Nama Pembeli</label><input name="items[${itemIndex}][buyer_name]" required class="w-full min-h-[48px] rounded-2xl border-slate-200 bg-white px-4 text-base" placeholder="Contoh: Haji Usman"></div><div class="md:col-span-2"><label class="block text-xs font-bold text-slate-500 mb-1">Jenis Ikan</label><input name="items[${itemIndex}][fish_type]" class="w-full min-h-[48px] rounded-2xl border-slate-200 bg-white px-4 text-base" placeholder="Opsional"></div><div class="md:col-span-2"><label class="block text-xs font-bold text-slate-500 mb-1">Gabus</label><input type="number" min="1" name="items[${itemIndex}][box_count]" oninput="recalc()" required class="box-count w-full min-h-[48px] rounded-2xl border-slate-200 bg-white px-4 text-base"></div><div class="md:col-span-3"><label class="block text-xs font-bold text-slate-500 mb-1">Harga/Gabus</label><input type="number" min="0" name="items[${itemIndex}][price_per_box]" oninput="recalc()" required class="price-per-box w-full min-h-[48px] rounded-2xl border-slate-200 bg-white px-4 text-base"></div><div class="md:col-span-1 flex md:flex-col justify-between md:items-end gap-2"><div><div class="text-[11px] text-slate-400">Subtotal</div><div class="subtotal font-black whitespace-nowrap">Rp0</div></div><button type="button" onclick="removeItemRow(this)" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700">Hapus</button></div></div>`;
    wrapper.appendChild(div); itemIndex++; recalc();
}
function removeItemRow(btn){ if(document.querySelectorAll('.item-row').length > 1){ btn.closest('.item-row').remove(); recalc(); } }
['total_boxes','shipping_cost','unloading_cost_per_box','additional_expense'].forEach(id => document.getElementById(id)?.addEventListener('input', recalc));
recalc();
</script>
