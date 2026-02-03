<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login cashier using phone number and PIN
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
            'pin' => ['required', 'string', 'min:4', 'max:6'],
        ]);

        $phone = trim($request->phone_number);

        // Try exact match first, then normalize formats
        $user = User::where('phone_number', $phone)->first();

        if (! $user) {
            // Normalize: +263... → 0..., or 0... → +263...
            if (str_starts_with($phone, '+263')) {
                $normalized = '0'.substr($phone, 4);
            } elseif (str_starts_with($phone, '263')) {
                $normalized = '0'.substr($phone, 3);
            } elseif (str_starts_with($phone, '0')) {
                $normalized = '+263'.substr($phone, 1);
            } else {
                $normalized = null;
            }

            if ($normalized) {
                $user = User::where('phone_number', $normalized)->first();
            }
        }

        if (! $user) {
            throw ValidationException::withMessages([
                'phone_number' => ['No account found with this phone number.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'phone_number' => ['Your account has been deactivated. Please contact admin.'],
            ]);
        }

        if (! in_array($user->role, [User::ROLE_CASHIER, User::ROLE_MANAGER, User::ROLE_ADMIN])) {
            throw ValidationException::withMessages([
                'phone_number' => ['You do not have permission to access the POS.'],
            ]);
        }

        if (! Hash::check($request->pin, $user->pin)) {
            throw ValidationException::withMessages([
                'pin' => ['Invalid PIN.'],
            ]);
        }

        // Get user's primary branch
        $branch = $user->primaryBranch();

        if (! $branch && ! $user->isAdmin()) {
            throw ValidationException::withMessages([
                'phone_number' => ['You are not assigned to any branch.'],
            ]);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Create token
        $token = $user->createToken('pos-mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'tenant_id' => $user->tenant_id,
                'branch' => $branch ? [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                ] : null,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Get current user info
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'tenant_id' => $user->tenant_id,
                'branch' => $branch ? [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                ] : null,
            ],
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'phone_number' => 'sometimes|string|unique:users,phone_number,'.$user->id,
        ]);

        $user->update($validated);

        $branch = $user->primaryBranch();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'tenant_id' => $user->tenant_id,
                'branch' => $branch ? [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                ] : null,
            ],
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Switch branch (for users with multiple branches)
     */
    public function switchBranch(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $user = $request->user();

        if (! $user->hasAccessToBranch($request->branch_id)) {
            return response()->json([
                'message' => 'You do not have access to this branch.',
            ], 403);
        }

        // Update primary branch
        $user->branches()->updateExistingPivot($request->branch_id, ['is_primary' => true]);
        $user->branches()->where('branches.id', '!=', $request->branch_id)
            ->update(['branch_user.is_primary' => false]);

        $branch = $user->branches()->find($request->branch_id);

        return response()->json([
            'message' => 'Branch switched successfully',
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
            ],
        ]);
    }

    /**
     * Get user's available branches
     */
    public function branches(Request $request): JsonResponse
    {
        $user = $request->user();
        $branches = $user->branches()->select('branches.id', 'branches.name', 'branches.code')->get();

        return response()->json([
            'branches' => $branches,
        ]);
    }
}
