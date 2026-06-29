<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMonthlyClosingRequest;
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
            ->when($request->filled('month'), fn ($q) => $q->where('month', $request->month))
            ->when($request->filled('year'), fn ($q) => $q->where('year', $request->year))
            ->latest('year')
            ->latest('month')
            ->paginate(15)
            ->withQueryString();

        return view('monthly-closings.index', compact('closings'));
    }

    public function create(Request $request, MonthlyClosingService $service)
    {
        $ownerId = $request->user()->activeOwnerId();
        $preview = null;

        if ($request->filled(['month', 'year'])) {
            $preview = $service->preview($ownerId, (int) $request->month, (int) $request->year);
        }

        return view('monthly-closings.create', compact('preview'));
    }

    public function store(StoreMonthlyClosingRequest $request, MonthlyClosingService $service, InvoiceNumberService $numberService, AuditLogService $audit)
    {
        try {
            $closing = $service->close([
                'owner_id' => $request->user()->activeOwnerId(),
                'month' => $request->month,
                'year' => $request->year,
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
            'nonOperationalExpenses',
        ]);

        return view('monthly-closings.show', ['closing' => $monthlyClosing]);
    }

    public function print(MonthlyClosing $monthlyClosing)
    {
        $this->authorizeOwner($monthlyClosing);
        $monthlyClosing->load([
            'shipItems.invoiceItems.invoice',
            'shipItems.operationalExpenses',
            'nonOperationalExpenses',
        ]);

        return view('monthly-closings.print', ['closing' => $monthlyClosing]);
    }

    private function authorizeOwner(MonthlyClosing $closing): void
    {
        abort_unless((int) $closing->owner_id === (int) auth()->user()->activeOwnerId(), 403);
    }
}
