<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuyerRequest;
use App\Http\Requests\UpdateBuyerRequest;
use App\Models\Buyer;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    public function index(Request $request)
    {
        $buyers = Buyer::query()
            ->forOwner($request->user()->activeOwnerId())
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->search.'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('buyers.index', compact('buyers'));
    }

    public function create()
    {
        return view('buyers.create');
    }

    public function store(StoreBuyerRequest $request, AuditLogService $audit)
    {
        $buyer = Buyer::create([
            'owner_id' => $request->user()->activeOwnerId(),
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $audit->record('buyer.created', $buyer, null, $buyer->toArray());

        return redirect()->route('buyers.index')->with('success', 'Pembeli berhasil ditambahkan.');
    }

    public function edit(Buyer $buyer)
    {
        $this->authorizeOwner($buyer);

        return view('buyers.edit', compact('buyer'));
    }

    public function update(UpdateBuyerRequest $request, Buyer $buyer, AuditLogService $audit)
    {
        $this->authorizeOwner($buyer);
        $old = $buyer->toArray();

        $buyer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active'),
        ]);

        $audit->record('buyer.updated', $buyer, $old, $buyer->fresh()->toArray());

        return redirect()->route('buyers.index')->with('success', 'Pembeli berhasil diperbarui.');
    }

    public function destroy(Buyer $buyer, AuditLogService $audit)
    {
        $this->authorizeOwner($buyer);
        $buyer->update(['is_active' => false]);
        $audit->record('buyer.deactivated', $buyer, null, ['is_active' => false]);

        return redirect()->route('buyers.index')->with('success', 'Pembeli berhasil dinonaktifkan.');
    }

    private function authorizeOwner(Buyer $buyer): void
    {
        abort_unless((int) $buyer->owner_id === (int) auth()->user()->activeOwnerId(), 403);
    }
}
