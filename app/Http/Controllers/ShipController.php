<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipRequest;
use App\Http\Requests\UpdateShipRequest;
use App\Models\Captain;
use App\Models\Ship;
use App\Services\AuditLogService;
use App\Services\ShipCaptainAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShipController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = $request->user()->activeOwnerId();

        $ships = Ship::query()
            ->with('activeCaptainAssignment.captain')
            ->forOwner($ownerId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('code', 'like', '%'.$request->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('ships.index', compact('ships'));
    }

    public function create()
    {
        return view('ships.create');
    }

    public function store(StoreShipRequest $request, ShipCaptainAssignmentService $assignmentService, AuditLogService $audit)
    {
        $ownerId = $request->user()->activeOwnerId();

        $ship = Ship::create([
            'owner_id' => $ownerId,
            'code' => $request->code ?: 'KPL-'.strtoupper(Str::random(6)),
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $captain = $this->findOrCreateCaptain($ownerId, $request->captain_name, $request->captain_phone);
        $assignmentService->assign($ship, $captain, $request->captain_start_date);

        $audit->record('ship.created', $ship, null, $ship->toArray());

        return redirect()->route('ships.index')->with('success', 'Kapal berhasil ditambahkan.');
    }

    public function show(Ship $ship)
    {
        $this->authorizeOwner($ship);

        $ship->load(['activeCaptainAssignment.captain', 'captainAssignments.captain']);
        $invoices = $ship->invoices()->latest('invoice_date')->paginate(10);

        return view('ships.show', compact('ship', 'invoices'));
    }

    public function edit(Ship $ship)
    {
        $this->authorizeOwner($ship);
        $ship->load('activeCaptainAssignment.captain');

        return view('ships.edit', compact('ship'));
    }

    public function update(UpdateShipRequest $request, Ship $ship, ShipCaptainAssignmentService $assignmentService, AuditLogService $audit)
    {
        $this->authorizeOwner($ship);
        $old = $ship->toArray();
        $ownerId = $request->user()->activeOwnerId();

        $ship->update([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        $ship->load('activeCaptainAssignment.captain');
        $captain = $this->findOrCreateCaptain($ownerId, $request->captain_name, $request->captain_phone);
        $currentCaptainId = $ship->activeCaptainAssignment?->captain_id;

        if ((int) $currentCaptainId !== (int) $captain->id) {
            $assignmentService->assign($ship, $captain, $request->captain_start_date);
        } elseif ($request->filled('captain_phone') && $captain->phone !== $request->captain_phone) {
            $captain->update(['phone' => $request->captain_phone]);
        }

        $audit->record('ship.updated', $ship, $old, $ship->fresh()->toArray());

        return redirect()->route('ships.index')->with('success', 'Kapal berhasil diperbarui.');
    }

    public function destroy(Ship $ship, AuditLogService $audit)
    {
        $this->authorizeOwner($ship);
        $ship->update(['is_active' => false]);
        $audit->record('ship.deactivated', $ship, null, ['is_active' => false]);

        return redirect()->route('ships.index')->with('success', 'Kapal berhasil dinonaktifkan.');
    }

    private function findOrCreateCaptain(int $ownerId, string $name, ?string $phone = null): Captain
    {
        $name = trim($name);
        $phone = $phone ? trim($phone) : null;

        $captain = Captain::query()
            ->forOwner($ownerId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($captain) {
            $updates = ['is_active' => true];
            if ($phone && $captain->phone !== $phone) {
                $updates['phone'] = $phone;
            }
            $captain->update($updates);

            return $captain;
        }

        return Captain::create([
            'owner_id' => $ownerId,
            'name' => $name,
            'phone' => $phone,
            'is_active' => true,
        ]);
    }

    private function authorizeOwner(Ship $ship): void
    {
        abort_unless((int) $ship->owner_id === (int) auth()->user()->activeOwnerId(), 403);
    }
}
