<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMonthlyClosingRequest;
use App\Http\Requests\UpdateMonthlyClosingRequest;
use App\Models\MonthlyClosing;
use App\Services\AuditLogService;
use App\Services\InvoiceNumberService;
use App\Services\MonthlyClosingService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MonthlyClosingController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = $request->user()->activeOwnerId();

        $closings = MonthlyClosing::query()
            ->forOwner($ownerId)
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim((string) $request->q);
                $query->where(function ($q) use ($keyword) {
                    $q->where('closing_number', 'like', '%'.$keyword.'%')
                        ->orWhere('period_label', 'like', '%'.$keyword.'%');
                });
            })
            ->latest('closing_period_number')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('monthly-closings.index', compact('closings'));
    }

    public function create(Request $request, MonthlyClosingService $service)
    {
        $ownerId = $request->user()->activeOwnerId();
        $preview = $service->preview($ownerId);

        $nextPeriodNumber = ((int) MonthlyClosing::query()
            ->where('owner_id', $ownerId)
            ->max('closing_period_number')) + 1;

        return view('monthly-closings.create', compact('preview', 'nextPeriodNumber'));
    }

    public function store(StoreMonthlyClosingRequest $request, MonthlyClosingService $service, InvoiceNumberService $numberService, AuditLogService $audit)
    {
        try {
            $closing = $service->close([
                'owner_id' => $request->user()->activeOwnerId(),
                'ships' => $request->input('ships', []),
                'notes' => $request->notes,
            ], $numberService);

            $audit->record('monthly_closing.created', $closing, null, $closing->toArray());

            return redirect()->route('monthly-closings.show', $closing)->with('success', 'Tutup bulan berhasil dibuat.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['closing' => $e->getMessage()]);
        }
    }

    public function show(MonthlyClosing $monthlyClosing)
    {
        $this->authorizeOwner($monthlyClosing);
        $monthlyClosing->load([
            'shipItems.invoiceItems.invoice',
            'shipItems.operationalExpenses',
        ]);

        return view('monthly-closings.show', ['closing' => $monthlyClosing]);
    }

    public function edit(MonthlyClosing $monthlyClosing, MonthlyClosingService $service)
    {
        $this->authorizeOwner($monthlyClosing);
        $monthlyClosing->load(['shipItems.invoiceItems', 'shipItems.operationalExpenses']);
        $preview = $service->previewFromClosing($monthlyClosing);

        return view('monthly-closings.edit', [
            'closing' => $monthlyClosing,
            'preview' => $preview,
        ]);
    }

    public function update(UpdateMonthlyClosingRequest $request, MonthlyClosing $monthlyClosing, MonthlyClosingService $service, AuditLogService $audit)
    {
        $this->authorizeOwner($monthlyClosing);
        $old = $monthlyClosing->toArray();

        try {
            $closing = $service->update($monthlyClosing, [
                'ships' => $request->input('ships', []),
                'notes' => $request->notes,
            ]);

            $audit->record('monthly_closing.updated', $closing, $old, $closing->toArray());

            return redirect()->route('monthly-closings.show', $closing)->with('success', 'Tutup bulan berhasil diperbarui.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['closing' => $e->getMessage()]);
        }
    }

    public function destroy(MonthlyClosing $monthlyClosing, MonthlyClosingService $service, AuditLogService $audit)
    {
        $this->authorizeOwner($monthlyClosing);
        $old = $monthlyClosing->load('shipItems.invoiceItems')->toArray();
        $service->delete($monthlyClosing);
        $audit->record('monthly_closing.deleted', $monthlyClosing, $old, ['deleted' => true]);

        return redirect()->route('monthly-closings.index')->with('success', 'Tutup bulan berhasil dihapus. Invoice harian kembali berstatus posted.');
    }

    public function screenshot(MonthlyClosing $monthlyClosing)
    {
        $this->authorizeOwner($monthlyClosing);
        $monthlyClosing->load([
            'shipItems.invoiceItems.invoice',
            'shipItems.operationalExpenses',
        ]);

        return view('monthly-closings.screenshot', ['closing' => $monthlyClosing]);
    }

    /**
     * Backward compatibility untuk link lama.
     */
    public function print(MonthlyClosing $monthlyClosing)
    {
        return $this->screenshot($monthlyClosing);
    }

    private function authorizeOwner(MonthlyClosing $closing): void
    {
        abort_unless((int) $closing->owner_id === (int) auth()->user()->activeOwnerId(), 403);
    }
}
