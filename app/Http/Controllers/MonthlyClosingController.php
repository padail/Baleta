<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMonthlyClosingRequest;
use App\Models\MonthlyClosing;
use App\Models\Ship;
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
            ->with(['ship', 'captain'])
            ->forOwner($ownerId)
            ->when($request->filled('ship_id'), fn ($q) => $q->where('ship_id', $request->ship_id))
            ->when($request->filled('month'), fn ($q) => $q->where('month', $request->month))
            ->when($request->filled('year'), fn ($q) => $q->where('year', $request->year))
            ->latest('year')
            ->latest('month')
            ->paginate(15)
            ->withQueryString();

        $ships = Ship::query()->forOwner($ownerId)->orderBy('name')->get();

        return view('monthly-closings.index', compact('closings', 'ships'));
    }

    public function create(Request $request, MonthlyClosingService $service)
    {
        $ownerId = $request->user()->activeOwnerId();
        $ships = Ship::query()->with('activeCaptainAssignment.captain')->forOwner($ownerId)->where('is_active', true)->orderBy('name')->get();
        $preview = null;

        if ($request->filled(['ship_id', 'month', 'year'])) {
            $preview = $service->preview($ownerId, (int) $request->ship_id, (int) $request->month, (int) $request->year);
        }

        return view('monthly-closings.create', compact('ships', 'preview'));
    }

    public function store(StoreMonthlyClosingRequest $request, MonthlyClosingService $service, InvoiceNumberService $numberService, AuditLogService $audit)
    {
        try {
            $closing = $service->close([
                'owner_id' => $request->user()->activeOwnerId(),
                'ship_id' => $request->ship_id,
                'month' => $request->month,
                'year' => $request->year,
                'captain_percentage' => $request->captain_percentage,
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
        $monthlyClosing->load(['ship', 'captain', 'items.invoice']);

        return view('monthly-closings.show', ['closing' => $monthlyClosing]);
    }

    public function print(MonthlyClosing $monthlyClosing)
    {
        $this->authorizeOwner($monthlyClosing);
        $monthlyClosing->load(['ship', 'captain', 'items.invoice']);

        return view('monthly-closings.print', ['closing' => $monthlyClosing]);
    }

    private function authorizeOwner(MonthlyClosing $closing): void
    {
        abort_unless((int) $closing->owner_id === (int) auth()->user()->activeOwnerId(), 403);
    }
}
