<?php

namespace App\Services;

use App\Models\FishDeliveryInvoice;
use App\Models\MonthlyClosing;

class InvoiceNumberService
{
    public function makeInvoiceNumber(int $ownerId, string $date): string
    {
        $ym = date('Ym', strtotime($date));

        $count = FishDeliveryInvoice::query()
            ->where('owner_id', $ownerId)
            ->whereYear('invoice_date', (int) substr($ym, 0, 4))
            ->whereMonth('invoice_date', (int) substr($ym, 4, 2))
            ->lockForUpdate()
            ->count() + 1;

        return 'INV-'.$ym.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Nomor tutup bulan tidak lagi mengikuti bulan kalender.
     * Setiap owner memakai urutan berjalan: TB-0001, TB-0002, dan seterusnya.
     */
    public function nextClosingPeriodNumber(int $ownerId): int
    {
        $max = MonthlyClosing::query()
            ->where('owner_id', $ownerId)
            ->lockForUpdate()
            ->max('closing_period_number');

        return ((int) $max) + 1;
    }

    public function makeClosingNumber(int $ownerId, ?int $periodNumber = null): string
    {
        $periodNumber ??= $this->nextClosingPeriodNumber($ownerId);

        return 'TB-'.str_pad((string) $periodNumber, 4, '0', STR_PAD_LEFT);
    }
}
