<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\FishDeliveryInvoice;
use App\Models\Ship;
use App\Services\AuditLogService;
use App\Services\InvoiceCalculationService;
use App\Services\InvoiceNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FishDeliveryInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = $request->user()->activeOwnerId();

        $invoices = FishDeliveryInvoice::query()
            ->with(['ship', 'captain'])
            ->forOwner($ownerId)
            ->when($request->filled('ship_id'), fn ($q) => $q->where('ship_id', $request->ship_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('invoice_date', $request->month))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('invoice_date', $request->year))
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $ships = Ship::query()->forOwner($ownerId)->orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'ships'));
    }

    public function create(Request $request)
    {
        $ownerId = $request->user()->activeOwnerId();
        $ships = Ship::query()
            ->with('activeCaptainAssignment.captain')
            ->forOwner($ownerId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('invoices.create', compact('ships'));
    }

    public function store(StoreInvoiceRequest $request, InvoiceCalculationService $calculator, InvoiceNumberService $numberService, AuditLogService $audit)
    {
        try {
            $invoice = DB::transaction(function () use ($request, $calculator, $numberService) {
                $ownerId = $request->user()->activeOwnerId();
                $payload = $request->validated();

                $ship = Ship::query()
                    ->with('activeCaptainAssignment')
                    ->forOwner($ownerId)
                    ->findOrFail($payload['ship_id']);

                $captainId = $ship->activeCaptainAssignment?->captain_id;
                if (! $captainId) {
                    throw new InvalidArgumentException('Kapal belum memiliki kapten aktif.');
                }

                $calculation = $calculator->calculateFromPayload($payload);

                $invoice = FishDeliveryInvoice::create([
                    'owner_id' => $ownerId,
                    'ship_id' => $ship->id,
                    'captain_id' => $captainId,
                    'invoice_number' => $numberService->makeInvoiceNumber($ownerId, $payload['invoice_date']),
                    'invoice_date' => $payload['invoice_date'],
                    'carrier_boat_name' => $payload['carrier_boat_name'] ?? null,
                    'total_boxes' => (int) $payload['total_boxes'],
                    'shipping_cost' => (int) ($payload['shipping_cost'] ?? 0),
                    'unloading_cost_per_box' => (int) ($payload['unloading_cost_per_box'] ?? 0),
                    'total_unloading_cost' => $calculation['total_unloading_cost'],
                    'additional_expense' => (int) ($payload['additional_expense'] ?? 0),
                    'total_expense' => $calculation['total_expense'],
                    'total_income' => $calculation['total_income'],
                    'net_income' => $calculation['net_income'],
                    'status' => FishDeliveryInvoice::STATUS_DRAFT,
                    'notes' => $payload['notes'] ?? null,
                    'sync_status' => 'synced',
                    'created_by' => auth()->id(),
                ]);

                $calculator->syncItemsAndExpenses($invoice, $payload);
                $calculator->recalculate($invoice);

                return $invoice->load(['ship', 'captain', 'items.buyer', 'expenses']);
            });

            $audit->record('invoice.created', $invoice, null, $invoice->toArray());

            return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice draft berhasil dibuat.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['invoice' => $e->getMessage()]);
        }
    }

    public function show(FishDeliveryInvoice $invoice)
    {
        $this->authorizeOwner($invoice);
        $invoice->load(['ship', 'captain', 'items.buyer', 'expenses']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Request $request, FishDeliveryInvoice $invoice)
    {
        $this->authorizeOwner($invoice);
        abort_unless($invoice->isEditable(), 403, 'Invoice hanya bisa diedit saat status draft.');

        $ownerId = $request->user()->activeOwnerId();
        $ships = Ship::query()
            ->with('activeCaptainAssignment.captain')
            ->forOwner($ownerId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $invoice->load(['items.buyer', 'expenses']);

        return view('invoices.edit', compact('invoice', 'ships'));
    }

    public function update(UpdateInvoiceRequest $request, FishDeliveryInvoice $invoice, InvoiceCalculationService $calculator, AuditLogService $audit)
    {
        $this->authorizeOwner($invoice);
        abort_unless($invoice->isEditable(), 403, 'Invoice hanya bisa diedit saat status draft.');

        try {
            DB::transaction(function () use ($request, $invoice, $calculator, $audit) {
                $ownerId = $request->user()->activeOwnerId();
                $payload = $request->validated();
                $old = $invoice->toArray();

                $ship = Ship::query()
                    ->with('activeCaptainAssignment')
                    ->forOwner($ownerId)
                    ->findOrFail($payload['ship_id']);

                $captainId = $ship->activeCaptainAssignment?->captain_id;
                if (! $captainId) {
                    throw new InvalidArgumentException('Kapal belum memiliki kapten aktif.');
                }

                $calculation = $calculator->calculateFromPayload($payload);

                $invoice->update([
                    'ship_id' => $ship->id,
                    'captain_id' => $captainId,
                    'invoice_date' => $payload['invoice_date'],
                    'carrier_boat_name' => $payload['carrier_boat_name'] ?? null,
                    'total_boxes' => (int) $payload['total_boxes'],
                    'shipping_cost' => (int) ($payload['shipping_cost'] ?? 0),
                    'unloading_cost_per_box' => (int) ($payload['unloading_cost_per_box'] ?? 0),
                    'total_unloading_cost' => $calculation['total_unloading_cost'],
                    'additional_expense' => (int) ($payload['additional_expense'] ?? 0),
                    'total_expense' => $calculation['total_expense'],
                    'total_income' => $calculation['total_income'],
                    'net_income' => $calculation['net_income'],
                    'notes' => $payload['notes'] ?? null,
                ]);

                $calculator->syncItemsAndExpenses($invoice, $payload);
                $calculator->recalculate($invoice);
                $audit->record('invoice.updated', $invoice, $old, $invoice->fresh()->toArray());
            });

            return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil diperbarui.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['invoice' => $e->getMessage()]);
        }
    }

    public function post(FishDeliveryInvoice $invoice, InvoiceCalculationService $calculator, AuditLogService $audit)
    {
        $this->authorizeOwner($invoice);
        abort_unless($invoice->status === FishDeliveryInvoice::STATUS_DRAFT, 403, 'Hanya invoice draft yang bisa diposting.');

        $old = $invoice->toArray();
        $calculator->recalculate($invoice);
        $invoice->update([
            'status' => FishDeliveryInvoice::STATUS_POSTED,
            'posted_at' => now(),
        ]);

        $audit->record('invoice.posted', $invoice, $old, $invoice->fresh()->toArray());

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil diposting.');
    }

    public function cancel(FishDeliveryInvoice $invoice, AuditLogService $audit)
    {
        $this->authorizeOwner($invoice);
        abort_if($invoice->status === FishDeliveryInvoice::STATUS_CLOSED, 403, 'Invoice closed tidak bisa dibatalkan langsung.');

        $old = $invoice->toArray();
        $invoice->update(['status' => FishDeliveryInvoice::STATUS_CANCELLED]);
        $audit->record('invoice.cancelled', $invoice, $old, $invoice->fresh()->toArray());

        return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dibatalkan.');
    }

    public function print(FishDeliveryInvoice $invoice)
    {
        $this->authorizeOwner($invoice);
        $invoice->load(['ship', 'captain', 'items.buyer', 'expenses']);

        return view('invoices.print', compact('invoice'));
    }

    private function authorizeOwner(FishDeliveryInvoice $invoice): void
    {
        abort_unless((int) $invoice->owner_id === (int) auth()->user()->activeOwnerId(), 403);
    }
}
