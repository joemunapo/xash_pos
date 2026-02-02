<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $branches = Branch::where('tenant_id', $tenantId)
            ->withCount('users')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('Admin/Branches/Index', [
            'branches' => $branches,
            'filters' => $request->only(['search', 'status', 'sort_by', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Branches/Create');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20', Rule::unique('branches')->where('tenant_id', $user->tenant_id)],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'receipt_header' => ['nullable', 'string', 'max:255'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['tenant_id'] = $user->tenant_id;
        $validated['is_active'] = true;

        $branch = Branch::create($validated);

        ActivityLog::log(
            ActivityLog::ACTION_CREATED,
            $user->tenant_id,
            $user->id,
            $branch->id,
            Branch::class,
            $branch->id,
            null,
            $branch->toArray()
        );

        return redirect()->route('admin.branches.show', $branch)
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch): Response
    {
        $this->authorizeAccess($branch);

        $branch->load(['users' => function ($query) {
            $query->select('users.id', 'users.name', 'users.email', 'users.role', 'users.is_active');
        }]);

        return Inertia::render('Admin/Branches/Show', [
            'branch' => $branch,
        ]);
    }

    public function edit(Branch $branch): Response
    {
        $this->authorizeAccess($branch);

        return Inertia::render('Admin/Branches/Edit', [
            'branch' => $branch,
        ]);
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorizeAccess($branch);
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20', Rule::unique('branches')->where('tenant_id', $user->tenant_id)->ignore($branch->id)],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'receipt_header' => ['nullable', 'string', 'max:255'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $oldValues = $branch->toArray();
        $branch->update($validated);

        ActivityLog::log(
            ActivityLog::ACTION_UPDATED,
            $user->tenant_id,
            $user->id,
            $branch->id,
            Branch::class,
            $branch->id,
            $oldValues,
            $branch->fresh()->toArray()
        );

        return redirect()->route('admin.branches.show', $branch)
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Request $request, Branch $branch)
    {
        $this->authorizeAccess($branch);
        $user = $request->user();

        ActivityLog::log(
            ActivityLog::ACTION_DELETED,
            $user->tenant_id,
            $user->id,
            $branch->id,
            Branch::class,
            $branch->id,
            $branch->toArray(),
            null
        );

        $branch->delete();

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch deleted successfully.');
    }

    protected function authorizeAccess(Branch $branch): void
    {
        if ($branch->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }
}
