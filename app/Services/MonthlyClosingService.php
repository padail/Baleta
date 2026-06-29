<?php

namespace App\Services;

use App\Models\FishDeliveryInvoice;
use App\Models\MonthlyClosing;
use App\Models\OwnerExpense;
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
            ->orderBy('invoice_date')
            ->orderBy('ship_id')
            ->get();

        $expenses = OwnerExpense::query()
            ->with('ship')
            ->forOwner($ownerId)
            ->posted()
            ->forPeriod($month, $year)
            ->orderBy('expense_date')
            ->orderBy('id')
            ->get();

        $shipSummaries = $invoices
            ->groupBy('ship_id')
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'ship_id' => $first->ship_id,
                    'ship_name' => $first->ship?->name ?? '-',
                    'captain_name' => $first->captain?->name ?? '-',
                    'total_invoices' => $group->count(),
                    'total_boxes' => (int) $group->sum('total_boxes'),
                    'total_income' => (int) $group->sum('total_income'),
                    'total_expense' => (int) $group->sum('total_expense'),
                    'net_income' => (int) $group->sum('net_income'),
                ];
            })
            ->values();

        $totalIncome = (int) $invoices->sum('total_income');
        $totalExpense = (int) $invoices->sum('total_expense');
        $dailyNetIncome = (int) $invoices->sum('net_income');
        $operationalExpenseTotal = (int) $expenses
            ->where('expense_type', OwnerExpense::TYPE_OPERATIONAL)
            ->sum('amount');
        $nonOperationalExpenseTotal = (int) $expenses
            ->where('expense_type', OwnerExpense::TYPE_NON_OPERATIONAL)
            ->sum('amount');
        $distributableIncome = $dailyNetIncome - $operationalExpenseTotal;

        $positiveShipNet = max(0, (int) $shipSummaries->sum(fn ($ship) => max(0, (int) $ship['net_income'])));
        $remainingOperational = $operationalExpenseTotal;
        $shipCount = $shipSummaries->count();
        $shipIndex = 0;
        $shipSummaries = $shipSummaries->map(function (array $ship) use ($operationalExpenseTotal, $positiveShipNet, &$remainingOperational, $shipCount, &$shipIndex) {
            $shipIndex++;
            $isLastShip = $shipIndex === $shipCount;

            if ($isLastShip) {
                $allocatedOperational = $remainingOperational;
            } elseif ($positiveShipNet > 0) {
                $allocatedOperational = (int) round($operationalExpenseTotal * (max(0, (int) $ship['net_income']) / $positiveShipNet));
                $allocatedOperational = min($allocatedOperational, $remainingOperational);
                $remainingOperational -= $allocatedOperational;
            } else {
                $allocatedOperational = 0;
            }

            $ship['operational_expense'] = $allocatedOperational;
            $ship['distributable_income'] = (int) $ship['net_income'] - $allocatedOperational;

            return $ship;
        });

        return [
            'invoices' => $invoices,
            'expenses' => $expenses,
            'operational_expenses' => $expenses->where('expense_type', OwnerExpense::TYPE_OPERATIONAL)->values(),
            'non_operational_expenses' => $expenses->where('expense_type', OwnerExpense::TYPE_NON_OPERATIONAL)->values(),
            'ship_summaries' => $shipSummaries,
            'total_ships' => $shipSummaries->count(),
            'total_invoices' => $invoices->count(),
            'total_boxes' => (int) $invoices->sum('total_boxes'),
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_income' => $dailyNetIncome,
            'daily_net_income' => $dailyNetIncome,
            'operational_expense_total' => $operationalExpenseTotal,
            'distributable_income' => $distributableIncome,
            'non_operational_expense_total' => $nonOperationalExpenseTotal,
        ];
    }

    public function close(array $payload, InvoiceNumberService $numberService): MonthlyClosing
    {
        return DB::transaction(function () use ($payload, $numberService) {
            $ownerId = (int) $payload['owner_id'];
            $month = (int) $payload['month'];
            $year = (int) $payload['year'];
            $captainPercentage = (float) $payload['captain_percentage'];

            $exists = MonthlyClosing::query()
                ->where('owner_id', $ownerId)
                ->where('month', $month)
                ->where('year', $year)
                ->where('status', '!=', MonthlyClosing::STATUS_CANCELLED)
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException('Tutup bulan untuk periode ini sudah dibuat. Satu owner hanya memiliki satu grand invoice per bulan.');
            }

            $preview = $this->preview($ownerId, $month, $year);

            if ($preview['total_invoices'] < 1) {
                throw new InvalidArgumentException('Tidak ada invoice posted untuk periode ini.');
            }

            $captainBase = max(0, (int) $preview['distributable_income']);
            $captainShare = (int) round($captainBase * $captainPercentage / 100);
            $ownerShare = (int) $preview['distributable_income'] - $captainShare;
            $ownerFinalIncome = $ownerShare - (int) $preview['non_operational_expense_total'];

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
                'operational_expense_total' => $preview['operational_expense_total'],
                'distributable_income' => $preview['distributable_income'],
                'captain_percentage' => $captainPercentage,
                'captain_share' => $captainShare,
                'owner_share' => $ownerShare,
                'non_operational_expense_total' => $preview['non_operational_expense_total'],
                'owner_final_income' => $ownerFinalIncome,
                'status' => MonthlyClosing::STATUS_APPROVED,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $payload['notes'] ?? null,
            ]);

            $positiveDailyNet = max(0, (int) $preview['invoices']->sum(fn ($invoice) => max(0, (int) $invoice->net_income)));
            $remainingOperational = (int) $preview['operational_expense_total'];
            $remainingCaptainShare = $captainShare;
            $remainingOwnerShare = $ownerShare;
            $invoiceCount = $preview['invoices']->count();
            $loopIndex = 0;

            foreach ($preview['invoices'] as $invoice) {
                $loopIndex++;
                $isLastInvoice = $loopIndex === $invoiceCount;
                $invoicePositiveNet = max(0, (int) $invoice->net_income);

                if ($isLastInvoice) {
                    $invoiceOperationalExpense = $remainingOperational;
                } elseif ($positiveDailyNet > 0) {
                    $invoiceOperationalExpense = (int) round($preview['operational_expense_total'] * ($invoicePositiveNet / $positiveDailyNet));
                    $invoiceOperationalExpense = min($invoiceOperationalExpense, $remainingOperational);
                } else {
                    $invoiceOperationalExpense = 0;
                }

                $invoiceDistributableIncome = (int) $invoice->net_income - $invoiceOperationalExpense;

                if ($isLastInvoice) {
                    $invoiceCaptainShare = $remainingCaptainShare;
                    $invoiceOwnerShare = $remainingOwnerShare;
                } else {
                    $invoiceCaptainShare = (int) round(max(0, $invoiceDistributableIncome) * $captainPercentage / 100);
                    $invoiceOwnerShare = $invoiceDistributableIncome - $invoiceCaptainShare;
                    $remainingCaptainShare -= $invoiceCaptainShare;
                    $remainingOwnerShare -= $invoiceOwnerShare;
                    $remainingOperational -= $invoiceOperationalExpense;
                }

                $closing->items()->create([
                    'invoice_id' => $invoice->id,
                    'ship_id' => $invoice->ship_id,
                    'captain_id' => $invoice->captain_id,
                    'ship_name' => $invoice->ship?->name,
                    'captain_name' => $invoice->captain?->name,
                    'invoice_date' => $invoice->invoice_date,
                    'total_boxes' => $invoice->total_boxes,
                    'total_income' => $invoice->total_income,
                    'total_expense' => $invoice->total_expense,
                    'net_income' => $invoice->net_income,
                    'operational_expense' => $invoiceOperationalExpense,
                    'distributable_income' => $invoiceDistributableIncome,
                    'captain_percentage' => $captainPercentage,
                    'captain_share' => $invoiceCaptainShare,
                    'owner_share' => $invoiceOwnerShare,
                ]);

                $invoice->update([
                    'status' => FishDeliveryInvoice::STATUS_CLOSED,
                    'closed_at' => now(),
                ]);
            }

            $preview['expenses']->each(function (OwnerExpense $expense) use ($closing) {
                $expense->update([
                    'monthly_closing_id' => $closing->id,
                    'status' => OwnerExpense::STATUS_CLOSED,
                    'closed_at' => now(),
                ]);
            });

            return $closing->load(['items.invoice', 'items.ship', 'items.captain', 'expenses.ship']);
        });
    }
}
