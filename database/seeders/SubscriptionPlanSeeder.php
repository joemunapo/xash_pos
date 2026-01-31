<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small businesses just getting started',
                'price_monthly' => 29.00,
                'price_yearly' => 290.00, // ~17% discount
                'max_users' => 3,
                'max_branches' => 1,
                'max_products' => 100,
                'features' => [
                    'Basic POS',
                    'Sales Reports',
                    'Inventory Management',
                    'Customer Management',
                    'Email Support',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing businesses with multiple locations',
                'price_monthly' => 79.00,
                'price_yearly' => 790.00, // ~17% discount
                'max_users' => 10,
                'max_branches' => 3,
                'max_products' => 1000,
                'features' => [
                    'All Starter features',
                    'Multi-branch Management',
                    'Advanced Reports',
                    'Purchase Orders',
                    'Supplier Management',
                    'Priority Email Support',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large businesses requiring unlimited access',
                'price_monthly' => 199.00,
                'price_yearly' => 1990.00, // ~17% discount
                'max_users' => 999999,
                'max_branches' => 999999,
                'max_products' => 999999,
                'features' => [
                    'All Professional features',
                    'Unlimited Users',
                    'Unlimited Branches',
                    'Unlimited Products',
                    'API Access',
                    'Custom Integrations',
                    'Dedicated Account Manager',
                    '24/7 Priority Support',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Subscription plans created successfully!');
    }
}
