<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Tanggal</label>
        <input type="date" name="expense_date" value="{{ old('expense_date', isset($expense) && $expense ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Jenis Pengeluaran</label>
        <select name="expense_type" required class="w-full rounded-lg border-slate-300">
            <option value="operational" @selected(old('expense_type', $expense->expense_type ?? '') === 'operational')>Operasional Bulanan</option>
            <option value="non_operational" @selected(old('expense_type', $expense->expense_type ?? '') === 'non_operational')>Non-Operasional</option>
        </select>
        <p class="text-xs text-slate-500 mt-1">Operasional mengurangi dasar pembagian. Non-operasional hanya tampil di rekap.</p>
    </div>
</div>
<div>
    <label class="block text-sm font-medium mb-1">Kapal</label>
    <select name="ship_id" class="w-full rounded-lg border-slate-300">
        <option value="">Umum atau semua kapal</option>
        @foreach($ships as $ship)
            <option value="{{ $ship->id }}" @selected((string) old('ship_id', $expense->ship_id ?? '') === (string) $ship->id)>{{ $ship->name }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium mb-1">Keterangan</label>
    <input name="description" value="{{ old('description', $expense->description ?? '') }}" required class="w-full rounded-lg border-slate-300" placeholder="Contoh: Solar, servis mesin, konsumsi kru, pinjaman pribadi">
</div>
<div>
    <label class="block text-sm font-medium mb-1">Nominal</label>
    <input type="number" min="0" name="amount" value="{{ old('amount', $expense->amount ?? 0) }}" required class="w-full rounded-lg border-slate-300">
</div>
<div>
    <label class="block text-sm font-medium mb-1">Catatan</label>
    <textarea name="notes" class="w-full rounded-lg border-slate-300">{{ old('notes', $expense->notes ?? '') }}</textarea>
</div>
