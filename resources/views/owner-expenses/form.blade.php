<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Tanggal</label>
        <input type="date" name="expense_date" value="{{ old('expense_date', isset($expense) && $expense ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="w-full rounded-xl border-slate-300 text-base">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Nominal</label>
        <input type="number" min="0" name="amount" value="{{ old('amount', $expense->amount ?? 0) }}" required class="w-full rounded-xl border-slate-300 text-base">
    </div>
</div>
<div>
    <label class="block text-sm font-medium mb-1">Keterangan</label>
    <input name="description" value="{{ old('description', $expense->description ?? '') }}" required class="w-full rounded-xl border-slate-300 text-base" placeholder="Contoh: biaya kantor, kendaraan owner, administrasi umum">
    <p class="text-xs text-slate-500 mt-1">Jangan masukkan biaya kebutuhan nelayan kapal di sini. Biaya operasional kapal diinput saat tutup bulan pada kartu masing-masing kapal.</p>
</div>
<div>
    <label class="block text-sm font-medium mb-1">Catatan</label>
    <textarea name="notes" class="w-full rounded-xl border-slate-300 min-h-28">{{ old('notes', $expense->notes ?? '') }}</textarea>
</div>
