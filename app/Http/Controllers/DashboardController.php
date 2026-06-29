<?php

namespace App\Http\Controllers;

use App\Models\FishDeliveryInvoice;
use App\Models\MonthlyClosing;
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

        $summary = [
            'total_income' => (int) (clone $invoiceBase)->sum('total_income'),
            'total_expense' => (int) (clone $invoiceBase)->sum('total_expense'),
            'net_income' => (int) (clone $invoiceBase)->sum('net_income'),
            'invoice_count' => (clone $invoiceBase)->count(),
            'active_ships' => Ship::query()->forOwner($ownerId)->where('is_active', true)->count(),
            'monthly_closing_count' => MonthlyClosing::query()->forOwner($ownerId)->where('month', $now->month)->where('year', $now->year)->count(),
        ];

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
