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

        $baseQuery = OwnerExpense::query()
            ->forOwner($ownerId)
            ->nonOperational()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('expense_date', $request->month))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('expense_date', $request->year));

        $expenses = (clone $baseQuery)
            ->latest('expense_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $summary = (clone $baseQuery)
            ->where('status', '!=', OwnerExpense::STATUS_CANCELLED)
            ->sum('amount');

        $monthlySummary = OwnerExpense::query()
            ->forOwner($ownerId)
            ->nonOperational()
            ->where('status', '!=', OwnerExpense::STATUS_CANCELLED)
            ->when($request->filled('year'), fn ($q) => $q->whereYear('expense_date', $request->year))
            ->selectRaw('YEAR(expense_date) as year, MONTH(expense_date) as month, COUNT(*) as total_records, SUM(amount) as total_amount')
            ->groupByRaw('YEAR(expense_date), MONTH(expense_date)')
            ->orderByRaw('year DESC, month DESC')
            ->limit(12)
            ->get();

        return view('owner-expenses.index', compact('expenses', 'summary', 'monthlySummary'));
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
        abort_if($expense->status === OwnerExpense::STATUS_CANCELLED, 422, 'Pengeluaran yang sudah dibatalkan tidak bisa diedit.');

        return view('owner-expenses.edit', compact('expense'));
    }

    public function update(UpdateOwnerExpenseRequest $request, OwnerExpense $expense, AuditLogService $audit)
    {
        $this->authorizeOwner($expense);
        abort_if($expense->status === OwnerExpense::STATUS_CANCELLED, 422, 'Pengeluaran yang sudah dibatalkan tidak bisa diedit.');

        $old = $expense->toArray();

        $expense->update([
            'expense_date' => $request->expense_date,
            'expense_type' => OwnerExpense::TYPE_NON_OPERATIONAL,
            'ship_id' => null,
            'monthly_closing_id' => null,
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
        abort_if($expense->status === OwnerExpense::STATUS_CANCELLED, 422, 'Pengeluaran ini sudah dibatalkan.');

        $expense->update(['status' => OwnerExpense::STATUS_CANCELLED]);
        $audit->record('owner_expense.cancelled', $expense, null, ['status' => OwnerExpense::STATUS_CANCELLED]);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran non-operasional berhasil dibatalkan.');
    }

    public function screenshot(Request $request)
    {
        $ownerId = $request->user()->activeOwnerId();

        $expenses = OwnerExpense::query()
            ->forOwner($ownerId)
            ->nonOperational()
            ->where('status', '!=', OwnerExpense::STATUS_CANCELLED)
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('expense_date', $request->month))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('expense_date', $request->year))
            ->orderBy('expense_date')
            ->orderBy('id')
            ->get();

        $total = (int) $expenses->sum('amount');

        return view('owner-expenses.screenshot', compact('expenses', 'total'));
    }

    /**
     * Backward compatibility untuk link lama.
     */
    public function print(Request $request)
    {
        return $this->screenshot($request);
    }

    private function authorizeOwner(OwnerExpense $expense): void
    {
        abort_unless((int) $expense->owner_id === (int) auth()->user()->activeOwnerId(), 403);
    }
}
