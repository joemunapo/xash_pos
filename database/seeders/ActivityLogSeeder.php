<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@xashpos.com')->first();
        if (! $admin) {
            return;
        }

        $tenantId = $admin->tenant_id;
        $users = User::where('tenant_id', $tenantId)->get();
        $branches = Branch::where('tenant_id', $tenantId)->get();

        // Generate login activities
        foreach ($users as $user) {
            for ($i = 0; $i < rand(3, 8); $i++) {
                ActivityLog::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $user->id,
                    'branch_id' => $branches->random()->id ?? null,
                    'action' => rand(0, 10) > 1 ? ActivityLog::ACTION_LOGIN : ActivityLog::ACTION_FAILED_LOGIN,
                    'model_type' => null,
                    'model_id' => null,
                    'old_values' => null,
                    'new_values' => null,
                    'ip_address' => $this->randomIp(),
                    'user_agent' => $this->randomUserAgent(),
                    'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                ]);
            }
        }

        // Generate product-related activities
        $products = Product::where('tenant_id', $tenantId)->limit(10)->get();
        foreach ($products as $product) {
            // Create activity
            ActivityLog::create([
                'tenant_id' => $tenantId,
                'user_id' => $users->random()->id,
                'branch_id' => $branches->random()->id ?? null,
                'action' => ActivityLog::ACTION_CREATED,
                'model_type' => Product::class,
                'model_id' => $product->id,
                'old_values' => null,
                'new_values' => [
                    'name' => $product->name,
                    'sku' => $product->sku,
                ],
                'ip_address' => $this->randomIp(),
                'user_agent' => $this->randomUserAgent(),
                'created_at' => $product->created_at,
            ]);

            // Update activity (for some products)
            if (rand(0, 1)) {
                ActivityLog::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $users->random()->id,
                    'branch_id' => $branches->random()->id ?? null,
                    'action' => ActivityLog::ACTION_UPDATED,
                    'model_type' => Product::class,
                    'model_id' => $product->id,
                    'old_values' => [
                        'price' => $product->selling_price - rand(1, 10),
                    ],
                   'new_values' => [
                        'price' => $product->selling_price,
                    ],
                    'ip_address' => $this->randomIp(),
                    'user_agent' => $this->randomUserAgent(),
                    'created_at' => $product->updated_at,
                ]);
            }
        }

        // Generate category activities
        $categories = Category::where('tenant_id', $tenantId)->limit(5)->get();
        foreach ($categories as $category) {
            ActivityLog::create([
                'tenant_id' => $tenantId,
                'user_id' => $users->random()->id,
                'branch_id' => null,
                'action' => ActivityLog::ACTION_CREATED,
                'model_type' => Category::class,
                'model_id' => $category->id,
                'old_values' => null,
                'new_values' => [
                    'name' => $category->name,
                    'description' => $category->description,
                ],
                'ip_address' => $this->randomIp(),
                'user_agent' => $this->randomUserAgent(),
                'created_at' => $category->created_at,
            ]);
        }

        // Generate customer activities
        $customers = Customer::where('tenant_id', $tenantId)->limit(5)->get();
        foreach ($customers as $customer) {
            ActivityLog::create([
                'tenant_id' => $tenantId,
                'user_id' => $users->random()->id,
                'branch_id' => $branches->random()->id ?? null,
                'action' => ActivityLog::ACTION_CREATED,
                'model_type' => Customer::class,
                'model_id' => $customer->id,
                'old_values' => null,
                'new_values' => [
                    'name' => $customer->full_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ],
                'ip_address' => $this->randomIp(),
                'user_agent' => $this->randomUserAgent(),
                'created_at' => $customer->created_at,
            ]);
        }

        $this->command->info('✓ Activity logs seeded successfully');
    }

    /**
     * Generate a random IP address.
     */
    private function randomIp(): string
    {
        $ips = [
            '192.168.1.'.rand(1, 255),
            '10.0.0.'.rand(1, 255),
            '172.16.0.'.rand(1, 255),
            '41.203.'.rand(1, 255).'.'.rand(1, 255), // Zimbabwe IP range
        ];

        return $ips[array_rand($ips)];
    }

    /**
     * Generate a random user agent.
     */
    private function randomUserAgent(): string
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        ];

        return $agents[array_rand($agents)];
    }
}
