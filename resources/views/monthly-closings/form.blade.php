@if($preview['total_invoices'] < 1)
    <div class="rounded-[1.75rem] bg-white p-6 md:p-8 shadow-sm border border-slate-100 text-center">
        <div class="mx-auto mb-4 h-14 w-14 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
        </div>
        <h2 class="text-xl font-black text-slate-900">Belum ada invoice siap tutup</h2>
        <p class="mt-2 text-sm text-slate-500 leading-relaxed">Posting invoice harian terlebih dahulu. Semua invoice berstatus sudah diposting akan masuk ke tutup bulan berikutnya.</p>
        <a href="{{ route('invoices.index', ['status' => 'draft']) }}" class="mt-5 inline-flex min-h-[48px] items-center rounded-2xl bg-[#073b3a] px-5 text-sm font-black text-white">Lihat Invoice</a>
    </div>
@else
<form method="POST" action="{{ $action }}" class="space-y-5" id="closingForm">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100 col-span-2 md:col-span-1">
            <div class="text-xs text-slate-500">{{ isset($closing) ? 'Nomor Rekap' : 'Tutup Bulan Berikutnya' }}</div>
            <div class="text-2xl font-black text-[#073b3a]">{{ isset($closing) ? $closing->display_period : 'Tutup Bulan '.$nextPeriodNumber }}</div>
        </div>
        <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100">
            <div class="text-xs text-slate-500">Kapal</div>
            <div class="text-2xl font-black">{{ $preview['total_ships'] }}</div>
        </div>
        <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100">
            <div class="text-xs text-slate-500">Invoice</div>
            <div class="text-2xl font-black">{{ $preview['total_invoices'] }}</div>
        </div>
        <div class="rounded-[1.5rem] bg-white p-4 shadow-sm border border-slate-100">
            <div class="text-xs text-slate-500">Rentang Tanggal</div>
            <div class="text-sm font-black leading-tight">{{ $preview['period_started_at'] ? $preview['period_started_at']->format('d/m/Y') : '-' }}<br>{{ $preview['period_ended_at'] ? $preview['period_ended_at']->format('d/m/Y') : '-' }}</div>
        </div>
        <div class="rounded-[1.5rem] bg-[#073b3a] text-white p-4 shadow-sm col-span-2 md:col-span-1">
            <div class="text-xs text-teal-100">Bersih Harian</div>
            <div class="text-xl font-black text-emerald-300">{{ \App\Support\Money::rupiah($preview['daily_net_income']) }}</div>
        </div>
    </div>

    <div class="rounded-[1.5rem] border border-teal-100 bg-teal-50 p-4 text-sm text-teal-950">
        <div class="font-black">Alur tutup bulan Baleta</div>
        <p class="mt-1 leading-relaxed">Periode tidak mengikuti bulan kalender. Setiap kali tutup bulan dibuat, sistem mengambil semua invoice yang sudah diposting yang belum ditutup, lalu memberi nomor urut seperti Tutup Bulan 1, Tutup Bulan 2, dan seterusnya.</p>
    </div>

    <div class="space-y-4" id="shipCards">
        @foreach($preview['ship_summaries'] as $ship)
            @php
                $oldShip = old('ships.'.$ship['ship_id'], []);
                $oldPercentage = $oldShip['captain_percentage'] ?? ($ship['captain_percentage'] ?? 20);
                $oldExpenses = $oldShip['operational_expenses'] ?? ($ship['operational_expenses'] ?? [
                    ['description' => 'Kebutuhan pokok nelayan', 'amount' => 0],
                    ['description' => 'Solar / BBM', 'amount' => 0],
                    ['description' => 'Es / air bersih', 'amount' => 0],
                ]);
            @endphp
            <section class="ship-card rounded-[1.75rem] bg-white shadow-sm border border-slate-100 overflow-hidden" data-ship-id="{{ $ship['ship_id'] }}" data-daily-net="{{ (int) $ship['total_daily_net_income'] }}">
                <div class="p-4 md:p-5 bg-[#073b3a] text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <h2 class="text-xl font-black">{{ $ship['ship_name'] }}</h2>
                            <p class="text-sm text-teal-100/80">Kapten: {{ $ship['captain_name'] }} · {{ $ship['total_invoices'] }} invoice harian</p>
                        </div>
                        <div class="md:text-right">
                            <div class="text-xs text-teal-100/70">Bersih Harian Kapal</div>
                            <div class="text-2xl font-black text-emerald-300">{{ \App\Support\Money::rupiah($ship['total_daily_net_income']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="p-4 md:p-5 grid lg:grid-cols-5 gap-4">
                    <div class="lg:col-span-2 rounded-2xl border border-slate-100 overflow-hidden">
                        <div class="px-4 py-3 bg-slate-50 font-black text-sm">Invoice Harian</div>
                        <div class="divide-y divide-slate-100 max-h-[320px] overflow-y-auto">
                            @foreach($ship['invoices'] as $invoice)
                                <div class="p-4 flex justify-between gap-3 text-sm">
                                    <div>
                                        <div class="font-bold">{{ $invoice->invoice_number }}</div>
                                        <div class="text-xs text-slate-500">{{ $invoice->invoice_date->format('d/m/Y') }} · {{ $invoice->total_boxes }} gabus</div>
                                    </div>
                                    <div class="font-black whitespace-nowrap">{{ \App\Support\Money::rupiah($invoice->net_income) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="lg:col-span-3 space-y-4">
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="font-black">Biaya Operasional Kapal</h3>
                                    <p class="text-xs text-slate-500">Bekal, kebutuhan nelayan, solar, es, air, dan biaya kapal lain.</p>
                                </div>
                                <button type="button" class="add-expense rounded-2xl bg-[#073b3a] text-white px-4 py-3 text-xs font-bold" data-ship-id="{{ $ship['ship_id'] }}">Tambah</button>
                            </div>
                            <div class="space-y-2 operational-list" id="operational-list-{{ $ship['ship_id'] }}">
                                @foreach($oldExpenses as $index => $expense)
                                    <div class="grid grid-cols-12 gap-2 operational-row">
                                        <input name="ships[{{ $ship['ship_id'] }}][operational_expenses][{{ $index }}][description]" value="{{ $expense['description'] ?? '' }}" class="col-span-7 min-h-[48px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base" placeholder="Keterangan">
                                        <input type="number" min="0" name="ships[{{ $ship['ship_id'] }}][operational_expenses][{{ $index }}][amount]" value="{{ $expense['amount'] ?? 0 }}" class="col-span-5 min-h-[48px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base operational-amount" placeholder="Nominal">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-sm font-black">Jasa Kapten</label>
                                <div class="rounded-full bg-teal-100 text-teal-700 px-3 py-1 font-black"><span class="captain-percent-label">{{ $oldPercentage }}</span>%</div>
                            </div>
                            <input type="range" min="0" max="100" step="1" name="ships[{{ $ship['ship_id'] }}][captain_percentage]" value="{{ $oldPercentage }}" class="captain-range w-full accent-teal-700">
                            <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                                <div class="rounded-2xl bg-white p-3 border border-slate-100"><div class="text-xs text-slate-500">Operasional</div><div class="font-black text-amber-700 operational-total-label">Rp0</div></div>
                                <div class="rounded-2xl bg-white p-3 border border-slate-100"><div class="text-xs text-slate-500">Setelah Op.</div><div class="font-black text-teal-700 after-operational-label">Rp0</div></div>
                                <div class="rounded-2xl bg-white p-3 border border-slate-100"><div class="text-xs text-slate-500">Untuk Kapten</div><div class="font-black captain-share-label">Rp0</div></div>
                                <div class="rounded-2xl bg-white p-3 border border-slate-100"><div class="text-xs text-slate-500">Untuk Pemilik</div><div class="font-black text-green-700 owner-share-label">Rp0</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endforeach
    </div>

    <div class="sticky bottom-24 md:static z-20 rounded-[1.75rem] bg-[#073b3a] text-white p-4 md:p-5 shadow-2xl shadow-teal-950/20">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
            <div><div class="text-xs text-teal-100/70">Bersih Harian</div><div class="font-black text-emerald-300" id="totalDailyNetLabel">Rp0</div></div>
            <div><div class="text-xs text-teal-100/70">Operasional</div><div class="font-black text-amber-200" id="totalOperationalLabel">Rp0</div></div>
            <div><div class="text-xs text-teal-100/70">Setelah Op.</div><div class="font-black text-cyan-200" id="totalAfterOperationalLabel">Rp0</div></div>
            <div><div class="text-xs text-teal-100/70">Jasa Kapten</div><div class="font-black" id="totalCaptainShareLabel">Rp0</div></div>
            <div class="col-span-2 md:col-span-1"><div class="text-xs text-teal-100/70">Hasil Pemilik dari Kapal</div><div class="font-black text-emerald-300" id="totalOwnerShareLabel">Rp0</div></div>
        </div>
    </div>

    <div class="rounded-[1.75rem] bg-white p-4 md:p-5 shadow-sm border border-slate-100 space-y-4">
        <div>
            <label class="block text-sm font-bold mb-2">Catatan</label>
            <textarea name="notes" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-base min-h-28" placeholder="Catatan tambahan untuk tutup bulan">{{ old('notes', $closing->notes ?? '') }}</textarea>
        </div>
        <button class="w-full md:w-auto min-h-[54px] bg-teal-600 text-white px-6 rounded-2xl font-black shadow-lg shadow-teal-700/20">{{ $submitLabel }}</button>
    </div>
</form>
<script>
function formatRp(n){ return 'Rp' + Number(Math.round(n || 0)).toLocaleString('id-ID'); }
function recalcClosing(){
    let totalDailyNet = 0, totalOperational = 0, totalAfterOperational = 0, totalCaptain = 0, totalOwner = 0;
    document.querySelectorAll('.ship-card').forEach(card => {
        const dailyNet = Number(card.dataset.dailyNet || 0);
        let operational = 0;
        card.querySelectorAll('.operational-amount').forEach(input => operational += Number(input.value || 0));
        const afterOperational = dailyNet - operational;
        const range = card.querySelector('.captain-range');
        const percent = Number(range.value || 0);
        const captain = Math.round(Math.max(0, afterOperational) * percent / 100);
        const owner = afterOperational - captain;
        totalDailyNet += dailyNet;
        totalOperational += operational;
        totalAfterOperational += afterOperational;
        totalCaptain += captain;
        totalOwner += owner;
        card.querySelector('.captain-percent-label').innerText = percent;
        card.querySelector('.operational-total-label').innerText = formatRp(operational);
        card.querySelector('.after-operational-label').innerText = formatRp(afterOperational);
        card.querySelector('.captain-share-label').innerText = formatRp(captain);
        card.querySelector('.owner-share-label').innerText = formatRp(owner);
    });
    document.getElementById('totalDailyNetLabel').innerText = formatRp(totalDailyNet);
    document.getElementById('totalOperationalLabel').innerText = formatRp(totalOperational);
    document.getElementById('totalAfterOperationalLabel').innerText = formatRp(totalAfterOperational);
    document.getElementById('totalCaptainShareLabel').innerText = formatRp(totalCaptain);
    document.getElementById('totalOwnerShareLabel').innerText = formatRp(totalOwner);
}
document.querySelectorAll('.captain-range, .operational-amount').forEach(el => el.addEventListener('input', recalcClosing));
document.querySelectorAll('.add-expense').forEach(button => {
    button.addEventListener('click', () => {
        const shipId = button.dataset.shipId;
        const list = document.getElementById('operational-list-' + shipId);
        const index = list.querySelectorAll('.operational-row').length;
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-2 operational-row';
        row.innerHTML = `<input name="ships[${shipId}][operational_expenses][${index}][description]" class="col-span-7 min-h-[48px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base" placeholder="Keterangan"><input type="number" min="0" name="ships[${shipId}][operational_expenses][${index}][amount]" value="0" class="col-span-5 min-h-[48px] rounded-2xl border-slate-200 bg-slate-50 px-4 text-base operational-amount" placeholder="Nominal">`;
        list.appendChild(row);
        row.querySelector('.operational-amount').addEventListener('input', recalcClosing);
    });
});
recalcClosing();
</script>
@endif
