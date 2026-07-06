<?php

namespace App\Http\Controllers;

use App\Models\FishDeliveryInvoice;
use App\Models\MonthlyClosing;
use App\Models\OwnerExpense;
use App\Models\Ship;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $ownerId = $request->user()->activeOwnerId();
        $now = now();

        $invoiceBase = FishDeliveryInvoice::query()
            ->forOwner($ownerId)
            ->whereIn('status', [
                FishDeliveryInvoice::STATUS_POSTED,
                FishDeliveryInvoice::STATUS_CLOSED,
            ])
            ->whereYear('invoice_date', $now->year)
            ->whereMonth('invoice_date', $now->month);

        $closing = MonthlyClosing::query()
            ->forOwner($ownerId)
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->where('status', MonthlyClosing::STATUS_APPROVED)
            ->first();

        $summary = [
            'total_income' => (int) (clone $invoiceBase)->sum('total_income'),
            'total_expense' => (int) (clone $invoiceBase)->sum('total_expense'),
            'net_income' => (int) (clone $invoiceBase)->sum('net_income'),
            'invoice_count' => (clone $invoiceBase)->count(),
            'active_ships' => Ship::query()->forOwner($ownerId)->where('is_active', true)->count(),
            'monthly_closing_count' => $closing ? 1 : 0,
            'operational_expense_total' => (int) ($closing?->operational_expense_total ?? 0),
            'captain_share' => (int) ($closing?->captain_share ?? 0),
            'owner_share' => (int) ($closing?->owner_share ?? 0),
            'non_operational_expense_total' => (int) OwnerExpense::query()
                ->forOwner($ownerId)
                ->nonOperational()
                ->where('status', '!=', OwnerExpense::STATUS_CANCELLED)
                ->whereYear('expense_date', $now->year)
                ->whereMonth('expense_date', $now->month)
                ->sum('amount'),
        ];

        $summary['distributable_income'] = $summary['net_income'] - $summary['operational_expense_total'];

        $latestInvoices = FishDeliveryInvoice::query()
            ->with(['ship', 'captain'])
            ->forOwner($ownerId)
            ->latest('invoice_date')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('dashboard', compact('summary', 'latestInvoices'));
    }
}
