<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOwnerExpenseRequest;
use App\Http\Requests\UpdateOwnerExpenseRequest;
use App\Models\OwnerExpense;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class OwnerExpenseController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = $request->user()->activeOwnerId();

        $expenses = OwnerExpense::query()
            ->forOwner($ownerId)
            ->nonOperational()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('expense_date', $request->month))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('expense_date', $request->year))
            ->latest('expense_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $summary = OwnerExpense::query()
            ->forOwner($ownerId)
            ->nonOperational()
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('expense_date', $request->month))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('expense_date', $request->year))
            ->where('status', '!=', OwnerExpense::STATUS_CANCELLED)
            ->sum('amount');

        return view('owner-expenses.index', compact('expenses', 'summary'));
    }

    public function create()
    {
        $expense = null;

        return view('owner-expenses.create', compact('expense'));
    }

    public function store(StoreOwnerExpenseRequest $request, AuditLogService $audit)
    {
        $expense = OwnerExpense::create([
            'owner_id' => $request->user()->activeOwnerId(),
            'expense_date' => $request->expense_date,
            'expense_type' => OwnerExpense::TYPE_NON_OPERATIONAL,
            'description' => $request->description,
            'amount' => (int) $request->amount,
            'status' => OwnerExpense::STATUS_POSTED,
            'created_by' => $request->user()->id,
            'notes' => $request->notes,
        ]);

        $audit->record('owner_expense.created', $expense, null, $expense->toArray());

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran non-operasional berhasil ditambahkan.');
    }

    public function edit(OwnerExpense $expense)
    {
        $this->authorizeOwner($expense);
        abort_if($expense->status === OwnerExpense::STATUS_CLOSED, 422, 'Pengeluaran yang sudah masuk tutup bulan tidak bisa diedit.');

        return view('owner-expenses.edit', compact('expense'));
    }

    public function update(UpdateOwnerExpenseRequest $request, OwnerExpense $expense, AuditLogService $audit)
    {
        $this->authorizeOwner($expense);
        abort_if($expense->status === OwnerExpense::STATUS_CLOSED, 422, 'Pengeluaran yang sudah masuk tutup bulan tidak bisa diedit.');

        $old = $expense->toArray();

        $expense->update([
            'expense_date' => $request->expense_date,
            'expense_type' => OwnerExpense::TYPE_NON_OPERATIONAL,
            'ship_id' => null,
            'description' => $request->description,
            'amount' => (int) $request->amount,
            'notes' => $request->notes,
        ]);

        $audit->record('owner_expense.updated', $expense, $old, $expense->fresh()->toArray());

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran non-operasional berhasil diperbarui.');
    }

    public function destroy(OwnerExpense $expense, AuditLogService $audit)
    {
        $this->authorizeOwner($expense);
        abort_if($expense->status === OwnerExpense::STATUS_CLOSED, 422, 'Pengeluaran yang sudah masuk tutup bulan tidak bisa dibatalkan.');

        $expense->update(['status' => OwnerExpense::STATUS_CANCELLED]);
        $audit->record('owner_expense.cancelled', $expense, null, ['status' => OwnerExpense::STATUS_CANCELLED]);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran non-operasional berhasil dibatalkan.');
    }

    private function authorizeOwner(OwnerExpense $expense): void
    {
        abort_unless((int) $expense->owner_id === (int) auth()->user()->activeOwnerId(), 403);
    }
}
