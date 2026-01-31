<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $users = User::where('company_id', $companyId)
            ->with('branches:id,name')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        $branches = Branch::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'branches' => $branches,
            'filters' => $request->only(['search', 'role', 'status', 'sort_by', 'sort_order']),
            'roles' => [
                ['value' => 'admin', 'label' => 'Admin'],
                ['value' => 'cashier', 'label' => 'Cashier'],
                ['value' => 'stockist', 'label' => 'Stockist'],
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        $branches = Branch::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Users/Create', [
            'branches' => $branches,
            'roles' => [
                ['value' => 'admin', 'label' => 'Admin'],
                ['value' => 'cashier', 'label' => 'Cashier'],
                ['value' => 'stockist', 'label' => 'Stockist'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'cashier', 'stockist'])],
            'pin' => ['nullable', 'numeric', 'digits_between:4,6'],
            'branches' => ['array'],
            'branches.*' => ['exists:branches,id'],
            'primary_branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $newUser = User::create([
            'company_id' => $currentUser->company_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => Hash::make($validated['password']),
            'pin' => isset($validated['pin']) ? Hash::make($validated['pin']) : null,
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        // Assign branches
        if (! empty($validated['branches'])) {
            foreach ($validated['branches'] as $branchId) {
                $newUser->branches()->attach($branchId, [
                    'role' => $validated['role'],
                    'is_primary' => $branchId == ($validated['primary_branch_id'] ?? $validated['branches'][0]),
                ]);
            }
        }

        ActivityLog::log(
            ActivityLog::ACTION_CREATED,
            $currentUser->company_id,
            $currentUser->id,
            null,
            User::class,
            $newUser->id,
            null,
            $newUser->only(['id', 'name', 'email', 'role'])
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): Response
    {
        $this->authorizeAccess($user);

        $user->load('branches:id,name');

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(Request $request, User $user): Response
    {
        $this->authorizeAccess($user);

        $currentUser = $request->user();
        $branches = Branch::where('company_id', $currentUser->company_id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        $user->load('branches:id,name');

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'branches' => $branches,
            'userBranchIds' => $user->branches->pluck('id'),
            'roles' => [
                ['value' => 'admin', 'label' => 'Admin'],
                ['value' => 'cashier', 'label' => 'Cashier'],
                ['value' => 'stockist', 'label' => 'Stockist'],
            ],
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAccess($user);
        $currentUser = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'cashier', 'stockist'])],
            'pin' => ['nullable', 'numeric', 'digits_between:4,6'],
            'is_active' => ['boolean'],
            'branches' => ['array'],
            'branches.*' => ['exists:branches,id'],
            'primary_branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $oldValues = $user->only(['name', 'email', 'role', 'is_active']);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? $user->phone_number,
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        if (isset($validated['pin'])) {
            $user->update(['pin' => Hash::make($validated['pin'])]);
        }

        // Sync branches
        if (isset($validated['branches'])) {
            $branchData = [];
            foreach ($validated['branches'] as $branchId) {
                $branchData[$branchId] = [
                    'role' => $validated['role'],
                    'is_primary' => $branchId == ($validated['primary_branch_id'] ?? ($validated['branches'][0] ?? null)),
                ];
            }
            $user->branches()->sync($branchData);
        }

        ActivityLog::log(
            ActivityLog::ACTION_UPDATED,
            $currentUser->company_id,
            $currentUser->id,
            null,
            User::class,
            $user->id,
            $oldValues,
            $user->only(['name', 'email', 'role', 'is_active'])
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeAccess($user);
        $currentUser = $request->user();

        // Prevent self-deletion
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        ActivityLog::log(
            ActivityLog::ACTION_DELETED,
            $currentUser->company_id,
            $currentUser->id,
            null,
            User::class,
            $user->id,
            $user->only(['id', 'name', 'email', 'role']),
            null
        );

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function resetPin(Request $request, User $user)
    {
        $this->authorizeAccess($user);

        $validated = $request->validate([
            'pin' => ['required', 'numeric', 'digits_between:4,6'],
        ]);

        $user->update(['pin' => Hash::make($validated['pin'])]);

        return back()->with('success', 'PIN reset successfully.');
    }

    protected function authorizeAccess(User $user): void
    {
        if ($user->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}
