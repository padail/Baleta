<?php

namespace App\Services;

use App\Models\FishDeliveryInvoice;
use App\Models\InvoiceExpense;

class InvoiceCalculationService
{
    public function calculateFromPayload(array $payload): array
    {
        $items = collect($payload['items'] ?? [])->filter(fn ($item) => filled($item['buyer_name'] ?? null));
        $totalBoxesFromItems = (int) $items->sum(fn ($item) => (int) ($item['box_count'] ?? 0));
        $totalIncome = (int) $items->sum(function ($item) {
            return (int) ($item['box_count'] ?? 0) * (int) ($item['price_per_box'] ?? 0);
        });

        $totalBoxes = (int) ($payload['total_boxes'] ?? 0);
        $shippingCost = (int) ($payload['shipping_cost'] ?? 0);
        $unloadingCostPerBox = (int) ($payload['unloading_cost_per_box'] ?? 0);
        $additionalExpense = (int) ($payload['additional_expense'] ?? 0);
        $totalUnloadingCost = $totalBoxes * $unloadingCostPerBox;
        $totalExpense = $shippingCost + $totalUnloadingCost + $additionalExpense;
        $netIncome = $totalIncome - $totalExpense;

        return [
            'total_boxes_from_items' => $totalBoxesFromItems,
            'total_income' => $totalIncome,
            'total_unloading_cost' => $totalUnloadingCost,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
        ];
    }

    /**
     * Revisi bisnis: total gabus detail pembeli tidak wajib sama dengan total gabus turun.
     * Method ini sengaja dibuat no-op agar controller lama atau test lama tidak rusak.
     */
    public function assertBoxCountValid(array $payload): void
    {
        return;
    }

    public function syncItemsAndExpenses(FishDeliveryInvoice $invoice, array $payload): void
    {
        $invoice->items()->delete();
        $invoice->expenses()->delete();

        foreach (($payload['items'] ?? []) as $item) {
            if (! filled($item['buyer_name'] ?? null)) {
                continue;
            }

            $boxCount = (int) ($item['box_count'] ?? 0);
            $pricePerBox = (int) ($item['price_per_box'] ?? 0);

            $invoice->items()->create([
                'buyer_id' => $item['buyer_id'] ?? null,
                'buyer_name' => trim((string) $item['buyer_name']),
                'fish_type' => $item['fish_type'] ?? null,
                'box_count' => $boxCount,
                'price_per_box' => $pricePerBox,
                'subtotal' => $boxCount * $pricePerBox,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $totalBoxes = (int) ($payload['total_boxes'] ?? 0);
        $shippingCost = (int) ($payload['shipping_cost'] ?? 0);
        $unloadingCostPerBox = (int) ($payload['unloading_cost_per_box'] ?? 0);
        $additionalExpense = (int) ($payload['additional_expense'] ?? 0);

        if ($shippingCost > 0) {
            $invoice->expenses()->create([
                'expense_type' => InvoiceExpense::TYPE_SHIPPING,
                'description' => 'Ongkir kapal '.($payload['carrier_boat_name'] ?? ''),
                'quantity' => 1,
                'unit_price' => $shippingCost,
                'amount' => $shippingCost,
            ]);
        }

        if ($unloadingCostPerBox > 0) {
            $invoice->expenses()->create([
                'expense_type' => InvoiceExpense::TYPE_UNLOADING,
                'description' => 'Jasa angkat gabus',
                'quantity' => $totalBoxes,
                'unit_price' => $unloadingCostPerBox,
                'amount' => $totalBoxes * $unloadingCostPerBox,
            ]);
        }

        if ($additionalExpense > 0) {
            $invoice->expenses()->create([
                'expense_type' => InvoiceExpense::TYPE_OTHER,
                'description' => 'Biaya tambahan',
                'quantity' => 1,
                'unit_price' => $additionalExpense,
                'amount' => $additionalExpense,
            ]);
        }
    }

    public function recalculate(FishDeliveryInvoice $invoice): FishDeliveryInvoice
    {
        $totalIncome = (int) $invoice->items()->sum('subtotal');
        $totalExpense = (int) $invoice->expenses()->sum('amount');

        $invoice->forceFill([
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_income' => $totalIncome - $totalExpense,
        ])->save();

        return $invoice->refresh();
    }
}
