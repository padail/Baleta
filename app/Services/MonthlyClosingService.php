<?php

namespace App\Services;

use App\Models\FishDeliveryInvoice;
use App\Models\MonthlyClosing;
use App\Models\Ship;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MonthlyClosingService
{
    public function preview(int $ownerId, int $shipId, int $month, int $year): array
    {
        $invoices = FishDeliveryInvoice::query()
            ->forOwner($ownerId)
            ->where('ship_id', $shipId)
            ->where('status', FishDeliveryInvoice::STATUS_POSTED)
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->orderBy('invoice_date')
            ->get();

        return [
            'invoices' => $invoices,
            'total_invoices' => $invoices->count(),
            'total_boxes' => (int) $invoices->sum('total_boxes'),
            'total_income' => (int) $invoices->sum('total_income'),
            'total_expense' => (int) $invoices->sum('total_expense'),
            'net_income' => (int) $invoices->sum('net_income'),
        ];
    }

    public function close(array $payload, InvoiceNumberService $numberService): MonthlyClosing
    {
        return DB::transaction(function () use ($payload, $numberService) {
            $ownerId = (int) $payload['owner_id'];
            $shipId = (int) $payload['ship_id'];
            $month = (int) $payload['month'];
            $year = (int) $payload['year'];
            $captainPercentage = (float) $payload['captain_percentage'];

            $exists = MonthlyClosing::query()
                ->where('owner_id', $ownerId)
                ->where('ship_id', $shipId)
                ->where('month', $month)
                ->where('year', $year)
                ->where('status', '!=', MonthlyClosing::STATUS_CANCELLED)
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException('Tutup bulan untuk kapal dan periode ini sudah dibuat.');
            }

            $preview = $this->preview($ownerId, $shipId, $month, $year);

            if ($preview['total_invoices'] < 1) {
                throw new InvalidArgumentException('Tidak ada invoice posted untuk periode ini.');
            }

            $ship = Ship::query()
                ->with('activeCaptainAssignment.captain')
                ->where('owner_id', $ownerId)
                ->findOrFail($shipId);

            $captainId = $ship->activeCaptainAssignment?->captain_id;

            if (! $captainId) {
                throw new InvalidArgumentException('Kapal belum memiliki kapten aktif.');
            }

            $captainShare = (int) round($preview['net_income'] * $captainPercentage / 100);
            $ownerShare = $preview['net_income'] - $captainShare;

            $closing = MonthlyClosing::create([
                'owner_id' => $ownerId,
                'ship_id' => $shipId,
                'captain_id' => $captainId,
                'closing_number' => $numberService->makeClosingNumber($ownerId, $year, $month),
                'month' => $month,
                'year' => $year,
                'total_invoices' => $preview['total_invoices'],
                'total_boxes' => $preview['total_boxes'],
                'total_income' => $preview['total_income'],
                'total_expense' => $preview['total_expense'],
                'net_income' => $preview['net_income'],
                'captain_percentage' => $captainPercentage,
                'captain_share' => $captainShare,
                'owner_share' => $ownerShare,
                'status' => MonthlyClosing::STATUS_APPROVED,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($preview['invoices'] as $invoice) {
                $closing->items()->create([
                    'invoice_id' => $invoice->id,
                    'invoice_date' => $invoice->invoice_date,
                    'total_boxes' => $invoice->total_boxes,
                    'total_income' => $invoice->total_income,
                    'total_expense' => $invoice->total_expense,
                    'net_income' => $invoice->net_income,
                ]);

                $invoice->update([
                    'status' => FishDeliveryInvoice::STATUS_CLOSED,
                    'closed_at' => now(),
                ]);
            }

            return $closing->load(['ship', 'captain', 'items.invoice']);
        });
    }
}
