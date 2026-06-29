<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $admins = User::query()
            ->where('owner_id', auth()->id())
            ->where('role', User::ROLE_ADMIN)
            ->orderBy('name')
            ->paginate(15);

        return view('admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admins.create');
    }

    public function store(StoreAdminRequest $request, AuditLogService $audit)
    {
        $admin = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_ADMIN,
            'owner_id' => auth()->id(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $audit->record('admin.created', $admin, null, $admin->only(['id', 'name', 'email', 'role', 'owner_id', 'is_active']));

        return redirect()->route('admins.index')->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit(User $admin)
    {
        $this->authorizeAdmin($admin);

        return view('admins.edit', compact('admin'));
    }

    public function update(UpdateAdminRequest $request, User $admin, AuditLogService $audit)
    {
        $this->authorizeAdmin($admin);
        $old = $admin->only(['name', 'email', 'phone', 'is_active']);

        $data = [
            'name' => $request->name,
            'email' => strtolower($request->email),
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);
        $audit->record('admin.updated', $admin, $old, $admin->only(['name', 'email', 'phone', 'is_active']));

        return redirect()->route('admins.index')->with('success', 'Admin berhasil diperbarui.');
    }

    public function destroy(User $admin, AuditLogService $audit)
    {
        $this->authorizeAdmin($admin);
        $admin->update(['is_active' => false]);
        $audit->record('admin.deactivated', $admin, null, ['is_active' => false]);

        return redirect()->route('admins.index')->with('success', 'Admin berhasil dinonaktifkan.');
    }

    private function authorizeAdmin(User $admin): void
    {
        abort_unless(
            $admin->role === User::ROLE_ADMIN && (int) $admin->owner_id === (int) auth()->id(),
            403
        );
    }
}
