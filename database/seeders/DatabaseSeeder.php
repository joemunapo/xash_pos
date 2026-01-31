<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // First create subscription plans
            SubscriptionPlanSeeder::class,
            
            // Then create super admin
            SuperAdminSeeder::class,
            
            // Then create demo tenant and users
            UserSeeder::class,
            
            // TODO: Fix these seeders - they need tenant_id updates
            // ProductSeeder::class,
            // ActivityLogSeeder::class,
        ]);
    }
}
