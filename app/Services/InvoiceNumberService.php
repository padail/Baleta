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

    public function makeClosingNumber(int $ownerId, int $year, int $month): string
    {
        $ym = sprintf('%04d%02d', $year, $month);

        $count = MonthlyClosing::query()
            ->where('owner_id', $ownerId)
            ->where('year', $year)
            ->where('month', $month)
            ->lockForUpdate()
            ->count() + 1;

        return 'CLOSE-'.$ym.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
