<?php

namespace App\Services;

use App\Models\FishDeliveryInvoice;
use App\Models\MonthlyClosing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MonthlyClosingService
{
    public function preview(int $ownerId, int $month, int $year): array
    {
        $invoices = FishDeliveryInvoice::query()
            ->with(['ship', 'captain'])
            ->forOwner($ownerId)
            ->where('status', FishDeliveryInvoice::STATUS_POSTED)
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->orderBy('ship_id')
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();

        $shipSummaries = $invoices
            ->groupBy('ship_id')
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'ship_id' => (int) $first->ship_id,
                    'ship_name' => $first->ship?->name ?? '-',
                    'captain_id' => $first->captain_id ? (int) $first->captain_id : null,
                    'captain_name' => $first->captain?->name ?? '-',
                    'invoices' => $group->values(),
                    'total_invoices' => $group->count(),
                    'total_boxes' => (int) $group->sum('total_boxes'),
                    'total_income' => (int) $group->sum('total_income'),
                    'total_invoice_expense' => (int) $group->sum('total_expense'),
                    'total_daily_net_income' => (int) $group->sum('net_income'),
                ];
            })
            ->sortBy('ship_name')
            ->values();

        return [
            'invoices' => $invoices,
            'ship_summaries' => $shipSummaries,
            'total_ships' => $shipSummaries->count(),
            'total_invoices' => $invoices->count(),
            'total_boxes' => (int) $invoices->sum('total_boxes'),
            'total_income' => (int) $invoices->sum('total_income'),
            'total_expense' => (int) $invoices->sum('total_expense'),
            'daily_net_income' => (int) $invoices->sum('net_income'),
        ];
    }

    public function close(array $payload, InvoiceNumberService $numberService): MonthlyClosing
    {
        return DB::transaction(function () use ($payload, $numberService) {
            $ownerId = (int) $payload['owner_id'];
            $month = (int) $payload['month'];
            $year = (int) $payload['year'];
            $shipPayload = $payload['ships'] ?? [];

            $exists = MonthlyClosing::query()
                ->where('owner_id', $ownerId)
                ->where('month', $month)
                ->where('year', $year)
                ->where('status', '!=', MonthlyClosing::STATUS_CANCELLED)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException('Tutup bulan untuk periode ini sudah dibuat. Satu owner hanya memiliki satu rekap final per bulan.');
            }

            $preview = $this->preview($ownerId, $month, $year);

            if ($preview['total_invoices'] < 1) {
                throw new InvalidArgumentException('Tidak ada invoice posted untuk periode ini.');
            }

            $shipResults = [];
            $totalShipOperationalExpense = 0;
            $totalAfterShipOperational = 0;
            $totalCaptainShare = 0;
            $totalOwnerShare = 0;
            $weightedCaptainPercentageTotal = 0;
            $weightedCaptainBaseTotal = 0;

            foreach ($preview['ship_summaries'] as $ship) {
                $shipId = (int) $ship['ship_id'];
                $input = $shipPayload[$shipId] ?? [];
                $captainPercentage = max(0, min(100, (float) ($input['captain_percentage'] ?? 0)));
                $operationalExpenses = $this->normalizeOperationalExpenses($input['operational_expenses'] ?? []);
                $shipOperationalTotal = (int) collect($operationalExpenses)->sum('amount');
                $netAfterOperational = (int) $ship['total_daily_net_income'] - $shipOperationalTotal;
                $captainBase = max(0, $netAfterOperational);
                $captainShare = (int) round($captainBase * $captainPercentage / 100);
                $ownerShare = $netAfterOperational - $captainShare;

                $totalShipOperationalExpense += $shipOperationalTotal;
                $totalAfterShipOperational += $netAfterOperational;
                $totalCaptainShare += $captainShare;
                $totalOwnerShare += $ownerShare;
                $weightedCaptainPercentageTotal += $captainPercentage * $captainBase;
                $weightedCaptainBaseTotal += $captainBase;

                $shipResults[] = [
                    'ship' => $ship,
                    'captain_percentage' => $captainPercentage,
                    'operational_expenses' => $operationalExpenses,
                    'ship_operational_total' => $shipOperationalTotal,
                    'net_after_operational' => $netAfterOperational,
                    'captain_share' => $captainShare,
                    'owner_share' => $ownerShare,
                ];
            }

            $weightedCaptainPercentage = $weightedCaptainBaseTotal > 0
                ? round($weightedCaptainPercentageTotal / $weightedCaptainBaseTotal, 2)
                : 0;

            $closing = MonthlyClosing::create([
                'owner_id' => $ownerId,
                'closing_number' => $numberService->makeClosingNumber($ownerId, $year, $month),
                'month' => $month,
                'year' => $year,
                'total_ships' => $preview['total_ships'],
                'total_invoices' => $preview['total_invoices'],
                'total_boxes' => $preview['total_boxes'],
                'total_income' => $preview['total_income'],
                'total_expense' => $preview['total_expense'],
                'net_income' => $preview['daily_net_income'],
                'daily_net_income' => $preview['daily_net_income'],
                'operational_expense_total' => $totalShipOperationalExpense,
                'distributable_income' => $totalAfterShipOperational,
                'captain_percentage' => $weightedCaptainPercentage,
                'captain_share' => $totalCaptainShare,
                'owner_share' => $totalOwnerShare,
                // Kolom lama diset 0 agar kompatibel dengan database lama, tetapi tidak dipakai lagi dalam rekap final.
                'non_operational_expense_total' => 0,
                'owner_final_income' => $totalOwnerShare,
                'status' => MonthlyClosing::STATUS_APPROVED,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($shipResults as $result) {
                $ship = $result['ship'];

                $shipItem = $closing->shipItems()->create([
                    'owner_id' => $ownerId,
                    'ship_id' => $ship['ship_id'],
                    'captain_id' => $ship['captain_id'],
                    'ship_name' => $ship['ship_name'],
                    'captain_name' => $ship['captain_name'],
                    'total_invoices' => $ship['total_invoices'],
                    'total_boxes' => $ship['total_boxes'],
                    'total_income' => $ship['total_income'],
                    'total_invoice_expense' => $ship['total_invoice_expense'],
                    'total_daily_net_income' => $ship['total_daily_net_income'],
                    'total_ship_operational_expense' => $result['ship_operational_total'],
                    'net_after_ship_operational' => $result['net_after_operational'],
                    'captain_percentage' => $result['captain_percentage'],
                    'captain_share' => $result['captain_share'],
                    'owner_share' => $result['owner_share'],
                ]);

                foreach ($result['operational_expenses'] as $expense) {
                    $shipItem->operationalExpenses()->create($expense);
                }

                foreach ($ship['invoices'] as $invoice) {
                    $shipItem->invoiceItems()->create([
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
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
            }

            return $closing->load([
                'shipItems.invoiceItems.invoice',
                'shipItems.operationalExpenses',
            ]);
        });
    }

    private function normalizeOperationalExpenses(array $expenses): array
    {
        return collect($expenses)
            ->map(function ($expense) {
                return [
                    'description' => trim((string) ($expense['description'] ?? '')),
                    'amount' => max(0, (int) ($expense['amount'] ?? 0)),
                ];
            })
            ->filter(fn (array $expense) => $expense['description'] !== '' || $expense['amount'] > 0)
            ->map(function (array $expense) {
                if ($expense['description'] === '') {
                    $expense['description'] = 'Biaya operasional kapal';
                }

                return $expense;
            })
            ->values()
            ->all();
    }
}
