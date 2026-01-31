<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed demo users for the application.
     */
    public function run(): void
    {
        // Create or get demo tenant
        $tenant = Tenant::firstOrCreate(
            ['email' => 'demo@xashpos.com'],
            [
                'name' => 'XASH Demo Store',
                'slug' => 'xash-demo-store',
                'trading_name' => 'XASH Demo',
                'registration_number' => 'REG-001',
                'vat_number' => 'VAT-001',
                'address' => '123 Main Street, Harare',
                'phone' => '+263771234567',
                'website' => 'https://xashpos.com',
                'default_currency' => 'USD',
                'fiscal_year_start' => 1,
                'is_active' => true,
                'subscription_status' => 'active',
            ]
        );

        // Create subscription for demo tenant
        if (!$tenant->activeSubscription) {
            $plan = SubscriptionPlan::where('slug', 'professional')->first();
            if ($plan) {
                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'subscription_plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'plan_slug' => $plan->slug,
                    'price' => $plan->price_monthly,
                    'billing_cycle' => 'monthly',
                    'max_users' => $plan->max_users,
                    'max_branches' => $plan->max_branches,
                    'max_products' => $plan->max_products,
                    'features' => $plan->features,
                    'starts_at' => now(),
                    'ends_at' => now()->addYear(),
                    'renews_at' => now()->addYear(),
                    'status' => 'active',
                ]);
            }
        }

        // Create main branch
        $mainBranch = Branch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'MAIN'],
            [
                'name' => 'Main Branch',
                'address' => '123 Main Street',
                'city' => 'Harare',
                'phone' => '+263771234567',
                'email' => 'main@xashpos.com',
                'is_active' => true,
            ]
        );

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@xashpos.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Attach admin to branch if not already
        if (! $admin->branches()->where('branch_id', $mainBranch->id)->exists()) {
            $admin->branches()->attach($mainBranch->id, [
                'role' => 'admin',
                'is_primary' => true,
            ]);
        }

        // Create cashier user
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@xashpos.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Cashier User',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'is_active' => true,
                'phone_number' => '0771049950',
                'pin' => Hash::make('2030'),
                'email_verified_at' => now(),
            ]
        );

        if (! $cashier->branches()->where('branch_id', $mainBranch->id)->exists()) {
            $cashier->branches()->attach($mainBranch->id, [
                'role' => 'cashier',
                'is_primary' => true,
            ]);
        }

        // Create branch manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@xashpos.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Branch Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
                'phone_number' => '0771234567',
                'pin' => Hash::make('1010'),
                'email_verified_at' => now(),
            ]
        );

        if (! $manager->branches()->where('branch_id', $mainBranch->id)->exists()) {
            $manager->branches()->attach($mainBranch->id, [
                'role' => 'manager',
                'is_primary' => true,
            ]);
        }
    }
}
