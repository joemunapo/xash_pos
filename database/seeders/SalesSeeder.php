<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SalesSeeder extends Seeder
{
    /**
     * Seed sales data for testing the sales summary report.
     */
    public function run(): void
    {
        // Get existing tenant
        $tenant = Tenant::where('email', 'demo@xashpos.com')->first();

        if (! $tenant) {
            $this->command->error('Please run UserSeeder first to create the tenant!');

            return;
        }

        // Get existing main branch from UserSeeder
        $mainBranch = Branch::where('tenant_id', $tenant->id)
            ->where('code', 'MAIN')
            ->first();

        if (! $mainBranch) {
            $this->command->error('Main branch not found. Please run UserSeeder first!');

            return;
        }

        // Create additional branches for variety
        $branch2 = Branch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'NORTH'],
            [
                'name' => 'North Branch',
                'address' => '456 North Avenue',
                'city' => 'Bulawayo',
                'phone' => '+263772345678',
                'email' => 'north@xashpos.com',
                'is_active' => true,
            ]
        );

        $branch3 = Branch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'SOUTH'],
            [
                'name' => 'South Branch',
                'address' => '789 South Street',
                'city' => 'Mutare',
                'phone' => '+263773456789',
                'email' => 'south@xashpos.com',
                'is_active' => true,
            ]
        );

        $branches = [$mainBranch, $branch2, $branch3];

        // Get existing users from UserSeeder
        $existingCashier = User::where('email', 'cashier@xashpos.com')->first();
        $existingManager = User::where('email', 'manager@xashpos.com')->first();

        // Create additional cashiers
        $cashier2 = User::firstOrCreate(
            ['email' => 'cashier2@xashpos.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'John Moyo',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'is_active' => true,
                'phone_number' => '0772345678',
                'pin' => Hash::make('1234'),
                'email_verified_at' => now(),
            ]
        );

        $cashier3 = User::firstOrCreate(
            ['email' => 'cashier3@xashpos.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Mary Ncube',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'is_active' => true,
                'phone_number' => '0773456789',
                'pin' => Hash::make('4567'),
                'email_verified_at' => now(),
            ]
        );

        $cashier4 = User::firstOrCreate(
            ['email' => 'cashier4@xashpos.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Peter Ndlovu',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'is_active' => true,
                'phone_number' => '0774567890',
                'pin' => Hash::make('7890'),
                'email_verified_at' => now(),
            ]
        );

        $cashiers = array_filter([$existingCashier, $cashier2, $cashier3, $cashier4]);

        if (empty($cashiers)) {
            $this->command->error('No cashiers found. Please run UserSeeder first!');

            return;
        }

        // Attach cashiers to branches
        foreach ($cashiers as $index => $cashier) {
            foreach ($branches as $branchIndex => $branch) {
                if (! $cashier->branches()->where('branch_id', $branch->id)->exists()) {
                    $cashier->branches()->attach($branch->id, [
                        'role' => 'cashier',
                        'is_primary' => $index === $branchIndex,
                    ]);
                }
            }
        }

        // Attach manager to all branches
        if ($existingManager) {
            foreach ($branches as $branchIndex => $branch) {
                if (! $existingManager->branches()->where('branch_id', $branch->id)->exists()) {
                    $existingManager->branches()->attach($branch->id, [
                        'role' => 'manager',
                        'is_primary' => $branchIndex === 0,
                    ]);
                }
            }
        }

        // Get products from ProductSeeder
        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        if ($products->isEmpty()) {
            $this->command->error('No products found. Please run ProductSeeder first!');

            return;
        }

        $this->command->info('Found '.count($products).' products:');
        foreach ($products as $product) {
            $this->command->info("  - {$product->name}: \${$product->selling_price}");
        }

        $paymentMethods = ['cash', 'card', 'mobile_money'];

        // Generate sales for the past 30 days
        $startDate = now()->subDays(30);
        $endDate = now();

        $this->command->info('Generating sales data...');
        $totalSales = 0;

        // Keep track of receipt counters per branch per date
        $receiptCounters = [];

        // Initialize counters based on existing sales
        foreach ($branches as $branch) {
            $existingSales = Sale::where('branch_id', $branch->id)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($existingSales as $sale) {
                $dateKey = $sale->created_at->format('Ymd');
                $branchKey = "{$dateKey}-{$branch->id}";

                if (preg_match('/-(\d{4})$/', $sale->receipt_number, $matches)) {
                    $sequence = (int) $matches[1];
                    if (! isset($receiptCounters[$branchKey]) || $receiptCounters[$branchKey] < $sequence) {
                        $receiptCounters[$branchKey] = $sequence;
                    }
                }
            }
        }

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Vary number of sales per day (more on weekends)
            $isWeekend = $date->isWeekend();
            $salesPerDay = $isWeekend ? rand(15, 30) : rand(8, 20);

            for ($i = 0; $i < $salesPerDay; $i++) {
                $branch = $branches[array_rand($branches)];
                $cashier = $cashiers[array_rand($cashiers)];

                // Random time during business hours (8 AM - 8 PM)
                $hour = rand(8, 19);
                $minute = rand(0, 59);
                $saleDateTime = $date->copy()->setTime($hour, $minute);

                // Generate unique receipt number
                $dateKey = $saleDateTime->format('Ymd');
                $branchKey = "{$dateKey}-{$branch->id}";

                if (! isset($receiptCounters[$branchKey])) {
                    $receiptCounters[$branchKey] = 1;
                } else {
                    $receiptCounters[$branchKey]++;
                }

                $receiptNumber = sprintf('RCP-%s-%d-%04d', $dateKey, $branch->id, $receiptCounters[$branchKey]);

                // Add 1-5 items per sale
                $itemCount = rand(1, 5);
                $subtotal = 0;
                $saleItems = [];

                for ($j = 0; $j < $itemCount; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 3);
                    $unitPrice = (float) $product->selling_price;
                    $costPrice = (float) ($product->cost_price ?? $unitPrice * 0.6);
                    $lineTotal = $unitPrice * $quantity;
                    $subtotal += $lineTotal;

                    $saleItems[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'quantity' => $quantity,
                        'unit' => $product->unit ?? 'pcs',
                        'unit_price' => $unitPrice,
                        'cost_price' => $costPrice,
                        'discount_amount' => 0,
                        'tax_amount' => 0,
                        'line_total' => $lineTotal,
                        'subtotal' => $lineTotal,
                    ];
                }

                // Calculate discount as a percentage of subtotal (0-10% of subtotal, 20% chance)
                $discountAmount = 0;
                if (rand(1, 100) <= 20) { // 20% chance of discount
                    $discountPercent = rand(5, 10); // 5-10% discount
                    $discountAmount = round($subtotal * ($discountPercent / 100), 2);
                }

                // Ensure total is always positive
                $totalAmount = max(0.01, $subtotal - $discountAmount);

                // Calculate payment
                $amountPaid = $totalAmount;
                $changeAmount = 0;

                // For cash payments, sometimes overpay
                if ($paymentMethods[array_rand($paymentMethods)] === 'cash') {
                    // Round up to nearest dollar or add small change
                    $amountPaid = ceil($totalAmount);
                    if (rand(1, 100) <= 30) {
                        $amountPaid += rand(0, 5);
                    }
                    $changeAmount = max(0, $amountPaid - $totalAmount);
                }

                $sale = Sale::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'user_id' => $cashier->id,
                    'receipt_number' => $receiptNumber,
                    'subtotal' => $subtotal,
                    'tax_amount' => 0,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'amount_paid' => $amountPaid,
                    'change_amount' => $changeAmount,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'status' => 'completed',
                    'completed_at' => $saleDateTime,
                    'created_at' => $saleDateTime,
                    'updated_at' => $saleDateTime,
                ]);

                // Create sale items
                foreach ($saleItems as $item) {
                    SaleItem::create(array_merge(['sale_id' => $sale->id], $item));
                }

                $totalSales++;
            }
        }

        // Create a few refunded sales (recent ones)
        $recentSales = Sale::where('status', 'completed')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $refundCount = 0;
        foreach ($recentSales->take(3) as $sale) {
            $sale->update(['status' => 'refunded']);
            $refundCount++;
        }

        $this->command->info("✓ Created {$totalSales} sales across ".count($branches).' branches');
        $this->command->info('✓ Sales distributed among '.count($cashiers).' cashiers');
        $this->command->info("✓ {$refundCount} sales marked as refunded");
        $this->command->info("✓ Date range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        // Display summary
        $totalRevenue = Sale::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        $avgSale = Sale::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->avg('total_amount');

        $this->command->info('');
        $this->command->info('Sales Summary:');
        $this->command->info('  Total Revenue: $'.number_format($totalRevenue, 2));
        $this->command->info('  Average Sale: $'.number_format($avgSale, 2));
    }
}
