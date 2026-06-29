<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaptainRequest;
use App\Http\Requests\UpdateCaptainRequest;
use App\Models\Captain;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class CaptainController extends Controller
{
    public function index(Request $request)
    {
        $captains = Captain::query()
            ->forOwner($request->user()->activeOwnerId())
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->search.'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('captains.index', compact('captains'));
    }

    public function create()
    {
        return view('captains.create');
    }

    public function store(StoreCaptainRequest $request, AuditLogService $audit)
    {
        $captain = Captain::create([
            'owner_id' => $request->user()->activeOwnerId(),
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $audit->record('captain.created', $captain, null, $captain->toArray());

        return redirect()->route('captains.index')->with('success', 'Kapten berhasil ditambahkan.');
    }

    public function edit(Captain $captain)
    {
        $this->authorizeOwner($captain);

        return view('captains.edit', compact('captain'));
    }

    public function update(UpdateCaptainRequest $request, Captain $captain, AuditLogService $audit)
    {
        $this->authorizeOwner($captain);
        $old = $captain->toArray();

        $captain->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => $request->boolean('is_active'),
        ]);

        $audit->record('captain.updated', $captain, $old, $captain->fresh()->toArray());

        return redirect()->route('captains.index')->with('success', 'Kapten berhasil diperbarui.');
    }

    public function destroy(Captain $captain, AuditLogService $audit)
    {
        $this->authorizeOwner($captain);
        $captain->update(['is_active' => false]);
        $audit->record('captain.deactivated', $captain, null, ['is_active' => false]);

        return redirect()->route('captains.index')->with('success', 'Kapten berhasil dinonaktifkan.');
    }

    private function authorizeOwner(Captain $captain): void
    {
        abort_unless((int) $captain->owner_id === (int) auth()->user()->activeOwnerId(), 403);
    }
}
