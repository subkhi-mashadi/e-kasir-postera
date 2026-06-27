<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    private function authorizeOwner(): void
    {
        abort_unless(auth()->user()->hasRole('owner'), 403);
    }

    public function index()
    {
        $this->authorizeOwner();

        $staff = User::where('company_id', auth()->user()->company_id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['cashier', 'branch_manager']))
            ->with('roles', 'branch')
            ->orderBy('name')
            ->get();

        return view('app.staff.index', compact('staff'));
    }

    public function create()
    {
        $this->authorizeOwner();

        $branches = Branch::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('app.staff.form', compact('branches'));
    }

    public function store(Request $request)
    {
        $this->authorizeOwner();

        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => ['required', Password::min(6)],
            'branch_id' => 'required|exists:branches,id',
            'role'      => 'required|in:cashier,branch_manager',
        ]);

        // Ensure branch belongs to same company
        $branch = Branch::where('id', $data['branch_id'])
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'company_id' => auth()->user()->company_id,
            'branch_id'  => $branch->id,
            'is_active'  => true,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('app.staff.index')->with('success', 'Akun staf berhasil ditambahkan.');
    }

    public function edit(User $staff)
    {
        $this->authorizeOwner();
        abort_unless($staff->company_id === auth()->user()->company_id, 403);

        $branches = Branch::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('app.staff.form', compact('staff', 'branches'));
    }

    public function update(Request $request, User $staff)
    {
        $this->authorizeOwner();
        abort_unless($staff->company_id === auth()->user()->company_id, 403);

        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email,' . $staff->id,
            'password'  => ['nullable', Password::min(6)],
            'branch_id' => 'required|exists:branches,id',
            'role'      => 'required|in:cashier,branch_manager',
            'is_active' => 'boolean',
        ]);

        $branch = Branch::where('id', $data['branch_id'])
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        $staff->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'branch_id' => $branch->id,
            'is_active' => $request->boolean('is_active', true),
            ...($data['password'] ? ['password' => Hash::make($data['password'])] : []),
        ]);

        $staff->syncRoles([$data['role']]);

        return redirect()->route('app.staff.index')->with('success', 'Akun staf berhasil diperbarui.');
    }

    public function destroy(User $staff)
    {
        $this->authorizeOwner();
        abort_unless($staff->company_id === auth()->user()->company_id, 403);

        $staff->delete();

        return back()->with('success', 'Akun staf dihapus.');
    }
}
