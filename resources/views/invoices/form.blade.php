<div class="grid md:grid-cols-3 gap-4">
    <div><label class="block text-sm font-medium mb-1">Tanggal</label><input type="date" name="invoice_date" value="{{ old('invoice_date', $invoice?->invoice_date?->toDateString() ?? now()->toDateString()) }}" required class="w-full rounded-lg border-slate-300"></div>
    <div><label class="block text-sm font-medium mb-1">Kapal</label><select name="ship_id" required class="w-full rounded-lg border-slate-300"><option value="">Pilih kapal</option>@foreach($ships as $ship)<option value="{{ $ship->id }}" @selected(old('ship_id', $invoice->ship_id ?? '') == $ship->id)>{{ $ship->name }} · Kapten: {{ $ship->activeCaptainAssignment?->captain?->name ?? '-' }}</option>@endforeach</select></div>
    <div><label class="block text-sm font-medium mb-1">Kapal Ongkir</label><input name="carrier_boat_name" value="{{ old('carrier_boat_name', $invoice->carrier_boat_name ?? '') }}" class="w-full rounded-lg border-slate-300"></div>
</div>
<div class="grid md:grid-cols-4 gap-4">
    <div><label class="block text-sm font-medium mb-1">Total Gabus Turun</label><input type="number" min="1" name="total_boxes" id="total_boxes" value="{{ old('total_boxes', $invoice->total_boxes ?? '') }}" required class="w-full rounded-lg border-slate-300"></div>
    <div><label class="block text-sm font-medium mb-1">Biaya Ongkir</label><input type="number" min="0" name="shipping_cost" id="shipping_cost" value="{{ old('shipping_cost', $invoice->shipping_cost ?? 0) }}" class="w-full rounded-lg border-slate-300 money-input"></div>
    <div><label class="block text-sm font-medium mb-1">Biaya Angkat per Gabus</label><input type="number" min="0" name="unloading_cost_per_box" id="unloading_cost_per_box" value="{{ old('unloading_cost_per_box', $invoice->unloading_cost_per_box ?? 0) }}" class="w-full rounded-lg border-slate-300 money-input"></div>
    <div><label class="block text-sm font-medium mb-1">Biaya Tambahan</label><input type="number" min="0" name="additional_expense" id="additional_expense" value="{{ old('additional_expense', $invoice->additional_expense ?? 0) }}" class="w-full rounded-lg border-slate-300 money-input"></div>
</div>
<div><label class="block text-sm font-medium mb-1">Catatan</label><textarea name="notes" class="w-full rounded-lg border-slate-300">{{ old('notes', $invoice->notes ?? '') }}</textarea></div>

<div class="border rounded-xl overflow-hidden" id="items-wrapper">
    <div class="bg-slate-50 p-3 flex justify-between items-center"><div><div class="font-semibold">Detail Pembeli</div><div class="text-xs text-slate-500">Nama pembeli langsung diketik. Tidak perlu membuat master data pembeli.</div></div><button type="button" onclick="addItemRow()" class="text-sm bg-slate-800 text-white px-3 py-1.5 rounded">Tambah Baris</button></div>
    <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr><th class="p-2 text-left">Nama Pembeli</th><th class="p-2 text-left">Jenis Ikan</th><th class="p-2 text-right">Gabus Dibeli</th><th class="p-2 text-right">Harga/Gabus</th><th class="p-2 text-right">Subtotal</th><th></th></tr></thead><tbody id="item-rows">
    @php $oldItems = old('items', $invoice?->items?->toArray() ?? [['buyer_name'=>'','fish_type'=>'','box_count'=>'','price_per_box'=>'','notes'=>'']]); @endphp
    @foreach($oldItems as $i => $item)
        <tr class="item-row border-t">
            <td class="p-2"><input name="items[{{ $i }}][buyer_name]" value="{{ $item['buyer_name'] ?? ($item['buyer']['name'] ?? '') }}" required class="rounded-lg border-slate-300 min-w-56" placeholder="Contoh: Haji Usman"></td>
            <td class="p-2"><input name="items[{{ $i }}][fish_type]" value="{{ $item['fish_type'] ?? '' }}" class="rounded-lg border-slate-300 min-w-32"></td>
            <td class="p-2"><input type="number" min="1" name="items[{{ $i }}][box_count]" value="{{ $item['box_count'] ?? '' }}" oninput="recalc()" required class="box-count rounded-lg border-slate-300 w-24 text-right"></td>
            <td class="p-2"><input type="number" min="0" name="items[{{ $i }}][price_per_box]" value="{{ $item['price_per_box'] ?? '' }}" oninput="recalc()" required class="price-per-box rounded-lg border-slate-300 w-32 text-right"></td>
            <td class="p-2 text-right subtotal">0</td>
            <td class="p-2"><button type="button" onclick="removeItemRow(this)" class="text-red-600">Hapus</button></td>
        </tr>
    @endforeach
    </tbody></table></div>
</div>

<div class="grid md:grid-cols-5 gap-4 bg-slate-50 rounded-xl p-4">
    <div><div class="text-xs text-slate-500">Gabus Dibeli / Turun</div><div class="font-bold" id="sum_boxes">0</div><div class="text-xs text-amber-600 mt-1" id="box_warning"></div></div>
    <div><div class="text-xs text-slate-500">Sisa Belum Terjual</div><div class="font-bold" id="remaining_boxes">0</div></div>
    <div><div class="text-xs text-slate-500">Total Pemasukan</div><div class="font-bold" id="sum_income">Rp0</div></div>
    <div><div class="text-xs text-slate-500">Total Pengeluaran</div><div class="font-bold" id="sum_expense">Rp0</div></div>
    <div><div class="text-xs text-slate-500">Pendapatan Bersih</div><div class="font-bold" id="sum_net">Rp0</div></div>
</div>

<script>
let itemIndex = document.querySelectorAll('.item-row').length;
function formatRp(n){ return 'Rp' + Number(n || 0).toLocaleString('id-ID'); }
function recalc(){
    let boxes = 0, income = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const b = Number(row.querySelector('.box-count')?.value || 0);
        const p = Number(row.querySelector('.price-per-box')?.value || 0);
        const sub = b * p;
        boxes += b; income += sub;
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
    document.getElementById('box_warning').innerText = remaining < 0 ? 'Gabus dibeli melebihi gabus turun. Tetap bisa disimpan sebagai draft.' : (remaining > 0 ? 'Masih ada gabus belum tercatat pembeli.' : '');
    document.getElementById('sum_income').innerText = formatRp(income);
    document.getElementById('sum_expense').innerText = formatRp(expense);
    document.getElementById('sum_net').innerText = formatRp(income - expense);
}
function addItemRow(){
    const tbody = document.getElementById('item-rows');
    const tr = document.createElement('tr'); tr.className='item-row border-t';
    tr.innerHTML = `<td class="p-2"><input name="items[${itemIndex}][buyer_name]" required class="rounded-lg border-slate-300 min-w-56" placeholder="Contoh: Haji Usman"></td><td class="p-2"><input name="items[${itemIndex}][fish_type]" class="rounded-lg border-slate-300 min-w-32"></td><td class="p-2"><input type="number" min="1" name="items[${itemIndex}][box_count]" oninput="recalc()" required class="box-count rounded-lg border-slate-300 w-24 text-right"></td><td class="p-2"><input type="number" min="0" name="items[${itemIndex}][price_per_box]" oninput="recalc()" required class="price-per-box rounded-lg border-slate-300 w-32 text-right"></td><td class="p-2 text-right subtotal">Rp0</td><td class="p-2"><button type="button" onclick="removeItemRow(this)" class="text-red-600">Hapus</button></td>`;
    tbody.appendChild(tr); itemIndex++; recalc();
}
function removeItemRow(btn){ if(document.querySelectorAll('.item-row').length > 1){ btn.closest('tr').remove(); recalc(); } }
['total_boxes','shipping_cost','unloading_cost_per_box','additional_expense'].forEach(id => document.getElementById(id)?.addEventListener('input', recalc));
recalc();
</script>
