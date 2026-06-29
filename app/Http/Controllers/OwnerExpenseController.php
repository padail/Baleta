<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOwnerExpenseRequest;
use App\Http\Requests\UpdateOwnerExpenseRequest;
use App\Models\OwnerExpense;
use App\Models\Ship;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class OwnerExpenseController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = $request->user()->activeOwnerId();

        $expenses = OwnerExpense::query()
            ->with('ship')
            ->forOwner($ownerId)
            ->when($request->filled('type'), fn ($q) => $q->where('expense_type', $request->type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('expense_date', $request->month))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('expense_date', $request->year))
            ->latest('expense_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $summary = OwnerExpense::query()
            ->forOwner($ownerId)
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('expense_date', $request->month))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('expense_date', $request->year))
            ->where('status', '!=', OwnerExpense::STATUS_CANCELLED)
            ->selectRaw("SUM(CASE WHEN expense_type = 'operational' THEN amount ELSE 0 END) as operational_total")
            ->selectRaw("SUM(CASE WHEN expense_type = 'non_operational' THEN amount ELSE 0 END) as non_operational_total")
            ->first();

        return view('owner-expenses.index', compact('expenses', 'summary'));
    }

    public function create(Request $request)
    {
        $ships = $this->ships($request);
        $expense = null;

        return view('owner-expenses.create', compact('ships', 'expense'));
    }

    public function store(StoreOwnerExpenseRequest $request, AuditLogService $audit)
    {
        $ownerId = $request->user()->activeOwnerId();
        $shipId = $this->validShipId($request, $ownerId);

        $expense = OwnerExpense::create([
            'owner_id' => $ownerId,
            'ship_id' => $shipId,
            'expense_date' => $request->expense_date,
            'expense_type' => $request->expense_type,
            'description' => $request->description,
            'amount' => (int) $request->amount,
            'status' => OwnerExpense::STATUS_POSTED,
            'created_by' => $request->user()->id,
            'notes' => $request->notes,
        ]);

        $audit->record('owner_expense.created', $expense, null, $expense->toArray());

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran rekap berhasil ditambahkan.');
    }

    public function edit(Request $request, OwnerExpense $expense)
    {
        $this->authorizeOwner($expense);
        abort_if($expense->status === OwnerExpense::STATUS_CLOSED, 422, 'Pengeluaran yang sudah masuk tutup bulan tidak bisa diedit.');

        $ships = $this->ships($request);

        return view('owner-expenses.edit', compact('expense', 'ships'));
    }

    public function update(UpdateOwnerExpenseRequest $request, OwnerExpense $expense, AuditLogService $audit)
    {
        $this->authorizeOwner($expense);
        abort_if($expense->status === OwnerExpense::STATUS_CLOSED, 422, 'Pengeluaran yang sudah masuk tutup bulan tidak bisa diedit.');

        $old = $expense->toArray();
        $ownerId = $request->user()->activeOwnerId();

        $expense->update([
            'ship_id' => $this->validShipId($request, $ownerId),
            'expense_date' => $request->expense_date,
            'expense_type' => $request->expense_type,
            'description' => $request->description,
            'amount' => (int) $request->amount,
            'notes' => $request->notes,
        ]);

        $audit->record('owner_expense.updated', $expense, $old, $expense->fresh()->toArray());

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran rekap berhasil diperbarui.');
    }

    public function destroy(OwnerExpense $expense, AuditLogService $audit)
    {
        $this->authorizeOwner($expense);
        abort_if($expense->status === OwnerExpense::STATUS_CLOSED, 422, 'Pengeluaran yang sudah masuk tutup bulan tidak bisa dibatalkan.');

        $expense->update(['status' => OwnerExpense::STATUS_CANCELLED]);
        $audit->record('owner_expense.cancelled', $expense, null, ['status' => OwnerExpense::STATUS_CANCELLED]);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran rekap berhasil dibatalkan.');
    }

    private function ships(Request $request)
    {
        return Ship::query()
            ->forOwner($request->user()->activeOwnerId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function validShipId(Request $request, int $ownerId): ?int
    {
        if (! $request->filled('ship_id')) {
            return null;
        }

        $ship = Ship::query()
            ->forOwner($ownerId)
            ->whereKey($request->ship_id)
            ->firstOrFail();

        return $ship->id;
    }

    private function authorizeOwner(OwnerExpense $expense): void
    {
        abort_unless((int) $expense->owner_id === (int) auth()->user()->activeOwnerId(), 403);
    }
}
