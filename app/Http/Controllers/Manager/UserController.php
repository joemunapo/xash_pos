<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get all users (cashiers) in the branch
     */
    public function index(Request $request): JsonResponse
    {
        $manager = $request->user();
        $branch = $manager->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
                'users' => [],
            ]);
        }

        $users = User::whereHas('branches', function ($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
            ->where('role', 'cashier')
            ->select('id', 'name', 'email', 'phone_number', 'is_active', 'created_at')
            ->withCount([
                'sales as total_sales' => function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id)
                        ->where('status', 'completed');
                },
            ])
            ->get();

        return response()->json($users);
    }

    /**
     * Get a single user details
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $manager = $request->user();
        $branch = $manager->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ], 403);
        }

        // Ensure the user belongs to the manager's branch
        if (! $user->branches()->where('branch_id', $branch->id)->exists()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $user->load('branches:id,name');
        $user->loadCount([
            'sales as total_sales' => function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                    ->where('status', 'completed');
            },
        ]);

        return response()->json($user);
    }

    /**
     * Create a new cashier
     */
    public function store(Request $request): JsonResponse
    {
        $manager = $request->user();
        $branch = $manager->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|unique:users,phone_number',
            'pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        // Create the user
        $user = User::create([
            'company_id' => $manager->company_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'pin' => bcrypt($validated['pin']),
            'password' => bcrypt($validated['pin']), // Use PIN as password as well
            'role' => User::ROLE_CASHIER,
            'is_active' => true,
        ]);

        // Attach user to the branch
        $user->branches()->attach($branch->id, [
            'role' => User::ROLE_CASHIER,
            'is_primary' => true,
        ]);

        return response()->json([
            'message' => 'Cashier created successfully',
            'user' => $user,
            'pin' => $validated['pin'], // Return the PIN so manager can share it
        ], 201);
    }

    /**
     * Update a cashier
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $manager = $request->user();
        $branch = $manager->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ], 403);
        }

        // Ensure the user belongs to the manager's branch and is a cashier
        if (! $user->branches()->where('branch_id', $branch->id)->exists() || $user->role !== User::ROLE_CASHIER) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'phone_number' => 'sometimes|string|unique:users,phone_number,'.$user->id,
            'pin' => 'sometimes|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        // Update basic fields
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['phone_number'])) {
            $user->phone_number = $validated['phone_number'];
        }

        // Update PIN if provided
        if (isset($validated['pin'])) {
            $user->pin = bcrypt($validated['pin']);
            $user->password = bcrypt($validated['pin']);
        }

        $user->save();

        return response()->json([
            'message' => 'Cashier updated successfully',
            'user' => $user,
            'pin' => $validated['pin'] ?? null, // Return new PIN if changed
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        $manager = $request->user();
        $branch = $manager->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ], 403);
        }

        // Ensure the user belongs to the manager's branch
        if (! $user->branches()->where('branch_id', $branch->id)->exists()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Don't allow managers to deactivate themselves
        if ($user->id === $manager->id) {
            return response()->json([
                'message' => 'You cannot deactivate yourself',
            ], 400);
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return response()->json([
            'message' => $user->is_active ? 'User activated successfully' : 'User deactivated successfully',
            'user' => $user,
        ]);
    }
}
