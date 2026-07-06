<?php

namespace App\Services;

use App\Models\FishDeliveryInvoice;
use App\Models\MonthlyClosing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MonthlyClosingService
{
    /**
     * Preview tutup bulan mengikuti cara kerja nelayan.
     * Periode tidak diambil dari bulan kalender. Semua invoice berstatus posted yang belum ditutup akan masuk preview.
     */
    public function preview(int $ownerId): array
    {
        $invoices = FishDeliveryInvoice::query()
            ->with(['ship', 'captain'])
            ->forOwner($ownerId)
            ->where('status', FishDeliveryInvoice::STATUS_POSTED)
            ->orderBy('ship_id')
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();

        $shipSummaries = $this->buildShipSummaries($invoices);

        return $this->makePreviewResult($invoices, $shipSummaries);
    }

    public function previewFromClosing(MonthlyClosing $closing): array
    {
        $closing->load(['shipItems.invoiceItems', 'shipItems.operationalExpenses']);

        $shipSummaries = $closing->shipItems
            ->map(function ($shipItem) {
                return [
                    'ship_id' => (int) $shipItem->ship_id,
                    'ship_name' => $shipItem->ship_name ?: '-',
                    'captain_id' => $shipItem->captain_id ? (int) $shipItem->captain_id : null,
                    'captain_name' => $shipItem->captain_name ?: '-',
                    'invoices' => $shipItem->invoiceItems,
                    'total_invoices' => (int) $shipItem->invoiceItems->count(),
                    'total_boxes' => (int) $shipItem->invoiceItems->sum('total_boxes'),
                    'total_income' => (int) $shipItem->invoiceItems->sum('total_income'),
                    'total_invoice_expense' => (int) $shipItem->invoiceItems->sum('total_expense'),
                    'total_daily_net_income' => (int) $shipItem->invoiceItems->sum('net_income'),
                    'captain_percentage' => (float) $shipItem->captain_percentage,
                    'operational_expenses' => $shipItem->operationalExpenses->map(fn ($expense) => [
                        'description' => $expense->description,
                        'amount' => (int) $expense->amount,
                    ])->values()->all(),
                ];
            })
            ->sortBy('ship_name')
            ->values();

        $invoiceItems = $closing->shipItems->flatMap->invoiceItems;

        return $this->makePreviewResult($invoiceItems, $shipSummaries);
    }

    public function close(array $payload, InvoiceNumberService $numberService): MonthlyClosing
    {
        return DB::transaction(function () use ($payload, $numberService) {
            $ownerId = (int) $payload['owner_id'];
            $shipPayload = $payload['ships'] ?? [];
            $preview = $this->preview($ownerId);

            if ($preview['total_invoices'] < 1) {
                throw new InvalidArgumentException('Belum ada invoice yang siap ditutup. Posting invoice harian terlebih dahulu.');
            }

            $periodNumber = $numberService->nextClosingPeriodNumber($ownerId);
            $closingNumber = $numberService->makeClosingNumber($ownerId, $periodNumber);
            $periodStartedAt = $preview['period_started_at'];
            $periodEndedAt = $preview['period_ended_at'];
            $shipResults = $this->calculateShipResults($preview['ship_summaries'], $shipPayload);
            $totals = $this->calculateClosingTotals($preview, $shipResults);
            $now = now();

            $closing = MonthlyClosing::create([
                'owner_id' => $ownerId,
                'closing_number' => $closingNumber,
                'closing_period_number' => $periodNumber,
                'period_label' => 'Tutup Bulan '.$periodNumber,
                // Kolom lama disimpan untuk kompatibilitas database, tetapi bukan dasar periode bisnis.
                'month' => (int) $now->month,
                'year' => (int) $now->year,
                'period_started_at' => $periodStartedAt,
                'period_ended_at' => $periodEndedAt,
                'total_ships' => $preview['total_ships'],
                'total_invoices' => $preview['total_invoices'],
                'total_boxes' => $preview['total_boxes'],
                'total_income' => $preview['total_income'],
                'total_expense' => $preview['total_expense'],
                'net_income' => $preview['daily_net_income'],
                'daily_net_income' => $preview['daily_net_income'],
                'operational_expense_total' => $totals['total_ship_operational_expense'],
                'distributable_income' => $totals['total_after_ship_operational'],
                'captain_percentage' => $totals['weighted_captain_percentage'],
                'captain_share' => $totals['total_captain_share'],
                'owner_share' => $totals['total_owner_share'],
                'non_operational_expense_total' => 0,
                'owner_final_income' => $totals['total_owner_share'],
                'status' => MonthlyClosing::STATUS_APPROVED,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => $now,
                'closed_at' => $now,
                'notes' => $payload['notes'] ?? null,
            ]);

            $this->persistShipResults($closing, $ownerId, $shipResults, true);

            return $closing->load(['shipItems.invoiceItems.invoice', 'shipItems.operationalExpenses']);
        });
    }

    public function update(MonthlyClosing $closing, array $payload): MonthlyClosing
    {
        return DB::transaction(function () use ($closing, $payload) {
            $closing->load(['shipItems.invoiceItems', 'shipItems.operationalExpenses']);
            $preview = $this->previewFromClosing($closing);
            $shipResults = $this->calculateShipResults($preview['ship_summaries'], $payload['ships'] ?? []);
            $totals = $this->calculateClosingTotals($preview, $shipResults);

            foreach ($shipResults as $result) {
                $ship = $result['ship'];
                $shipItem = $closing->shipItems->firstWhere('ship_id', $ship['ship_id']);

                if (! $shipItem) {
                    continue;
                }

                $shipItem->update([
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

                $shipItem->operationalExpenses()->delete();
                foreach ($result['operational_expenses'] as $expense) {
                    $shipItem->operationalExpenses()->create($expense);
                }
            }

            $periodStartedAt = $preview['period_started_at'] ?: $closing->period_started_at;
            $periodEndedAt = $preview['period_ended_at'] ?: $closing->period_ended_at;

            $closing->update([
                'period_started_at' => $periodStartedAt,
                'period_ended_at' => $periodEndedAt,
                'total_ships' => $preview['total_ships'],
                'total_invoices' => $preview['total_invoices'],
                'total_boxes' => $preview['total_boxes'],
                'total_income' => $preview['total_income'],
                'total_expense' => $preview['total_expense'],
                'net_income' => $preview['daily_net_income'],
                'daily_net_income' => $preview['daily_net_income'],
                'operational_expense_total' => $totals['total_ship_operational_expense'],
                'distributable_income' => $totals['total_after_ship_operational'],
                'captain_percentage' => $totals['weighted_captain_percentage'],
                'captain_share' => $totals['total_captain_share'],
                'owner_share' => $totals['total_owner_share'],
                'owner_final_income' => $totals['total_owner_share'],
                'notes' => $payload['notes'] ?? null,
            ]);

            return $closing->fresh(['shipItems.invoiceItems.invoice', 'shipItems.operationalExpenses']);
        });
    }

    public function delete(MonthlyClosing $closing): void
    {
        DB::transaction(function () use ($closing) {
            $closing->load('shipItems.invoiceItems');

            $invoiceIds = $closing->shipItems
                ->flatMap->invoiceItems
                ->pluck('invoice_id')
                ->filter()
                ->unique()
                ->values();

            FishDeliveryInvoice::query()
                ->whereIn('id', $invoiceIds)
                ->where('owner_id', $closing->owner_id)
                ->where('status', FishDeliveryInvoice::STATUS_CLOSED)
                ->update([
                    'status' => FishDeliveryInvoice::STATUS_POSTED,
                    'closed_at' => null,
                ]);

            $closing->delete();
        });
    }

    private function buildShipSummaries(Collection $invoices): Collection
    {
        return $invoices
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
    }

    private function makePreviewResult(Collection $invoices, Collection $shipSummaries): array
    {
        $dates = $invoices->pluck('invoice_date')->filter()->sort()->values();

        return [
            'invoices' => $invoices,
            'ship_summaries' => $shipSummaries,
            'total_ships' => $shipSummaries->count(),
            'total_invoices' => $invoices->count(),
            'total_boxes' => (int) $invoices->sum('total_boxes'),
            'total_income' => (int) $invoices->sum('total_income'),
            'total_expense' => (int) $invoices->sum('total_expense'),
            'daily_net_income' => (int) $invoices->sum('net_income'),
            'period_started_at' => $dates->first(),
            'period_ended_at' => $dates->last(),
        ];
    }

    private function calculateShipResults(Collection $shipSummaries, array $shipPayload): array
    {
        $shipResults = [];

        foreach ($shipSummaries as $ship) {
            $shipId = (int) $ship['ship_id'];
            $input = $shipPayload[$shipId] ?? [];
            $captainPercentage = max(0, min(100, (float) ($input['captain_percentage'] ?? ($ship['captain_percentage'] ?? 0))));
            $operationalExpenses = $this->normalizeOperationalExpenses($input['operational_expenses'] ?? ($ship['operational_expenses'] ?? []));
            $shipOperationalTotal = (int) collect($operationalExpenses)->sum('amount');
            $netAfterOperational = (int) $ship['total_daily_net_income'] - $shipOperationalTotal;
            $captainBase = max(0, $netAfterOperational);
            $captainShare = (int) round($captainBase * $captainPercentage / 100);
            $ownerShare = $netAfterOperational - $captainShare;

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

        return $shipResults;
    }

    private function calculateClosingTotals(array $preview, array $shipResults): array
    {
        $totalShipOperationalExpense = 0;
        $totalAfterShipOperational = 0;
        $totalCaptainShare = 0;
        $totalOwnerShare = 0;
        $weightedCaptainPercentageTotal = 0;
        $weightedCaptainBaseTotal = 0;

        foreach ($shipResults as $result) {
            $captainBase = max(0, (int) $result['net_after_operational']);
            $totalShipOperationalExpense += (int) $result['ship_operational_total'];
            $totalAfterShipOperational += (int) $result['net_after_operational'];
            $totalCaptainShare += (int) $result['captain_share'];
            $totalOwnerShare += (int) $result['owner_share'];
            $weightedCaptainPercentageTotal += ((float) $result['captain_percentage']) * $captainBase;
            $weightedCaptainBaseTotal += $captainBase;
        }

        return [
            'total_ship_operational_expense' => $totalShipOperationalExpense,
            'total_after_ship_operational' => $totalAfterShipOperational,
            'total_captain_share' => $totalCaptainShare,
            'total_owner_share' => $totalOwnerShare,
            'weighted_captain_percentage' => $weightedCaptainBaseTotal > 0
                ? round($weightedCaptainPercentageTotal / $weightedCaptainBaseTotal, 2)
                : 0,
        ];
    }

    private function persistShipResults(MonthlyClosing $closing, int $ownerId, array $shipResults, bool $lockInvoices): void
    {
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

                if ($lockInvoices) {
                    $invoice->update([
                        'status' => FishDeliveryInvoice::STATUS_CLOSED,
                        'closed_at' => now(),
                    ]);
                }
            }
        }
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
