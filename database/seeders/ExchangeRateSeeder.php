<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Seed exchange rates for multi-currency support.
     */
    public function run(): void
    {
        $tenant = Tenant::first();

        if (! $tenant) {
            $this->command->warn('No tenant found. Please run UserSeeder first.');

            return;
        }

        $today = now()->toDateString();

        // Current approximate rates (as of January 2026)
        $rates = [
            // USD to ZIG (Zimbabwe Gold)
            ['from' => 'USD', 'to' => 'ZIG', 'rate' => 13.50],
            ['from' => 'ZIG', 'to' => 'USD', 'rate' => 0.074074],

            // USD to ZAR (South African Rand)
            ['from' => 'USD', 'to' => 'ZAR', 'rate' => 18.50],
            ['from' => 'ZAR', 'to' => 'USD', 'rate' => 0.054054],

            // ZIG to ZAR
            ['from' => 'ZIG', 'to' => 'ZAR', 'rate' => 1.37],
            ['from' => 'ZAR', 'to' => 'ZIG', 'rate' => 0.729927],
        ];

        foreach ($rates as $rate) {
            ExchangeRate::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'from_currency' => $rate['from'],
                    'to_currency' => $rate['to'],
                    'effective_date' => $today,
                ],
                [
                    'rate' => $rate['rate'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✓ Exchange rates seeded successfully');
        $this->command->info('✓ Currencies supported: USD, ZIG, ZAR');
    }
}
