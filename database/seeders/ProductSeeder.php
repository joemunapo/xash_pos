<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::where('email', 'demo@xashpos.com')->first();
        if (! $tenant) {
            $this->command->error('Demo tenant not found. Run UserSeeder first.');

            return;
        }

        $branch = Branch::where('tenant_id', $tenant->id)->where('code', 'MAIN')->first();
        if (! $branch) {
            $this->command->error('Main branch not found. Run UserSeeder first.');

            return;
        }

        // Create Categories
        $this\->command\->info('Creating categories...');
        $beverages = Category::firstOrCreate(
            ['tenant_id' => $company\->id, 'slug' => 'beverages'],
            [
                'name' => 'Beverages',
                'description' => 'Drinks and beverages',
                'is_active' => true,
            ]
        );

        $groceries = Category::firstOrCreate(
            ['tenant_id' => $company\->id, 'slug' => 'groceries'],
            [
                'name' => 'Groceries',
                'description' => 'Essential food items',
                'is_active' => true,
            ]
        );

        $cookingOil = Category::firstOrCreate(
            ['tenant_id' => $company\->id, 'slug' => 'cooking-oils'],
            [
                'name' => 'Cooking Oils',
                'description' => 'Cooking oils and fats',
                'is_active' => true,
            ]
        );

        $wines = Category::firstOrCreate(
            ['tenant_id' => $company\->id, 'slug' => 'wines-spirits'],
            [
                'name' => 'Wines & Spirits',
                'description' => 'Alcoholic beverages',
                'is_active' => true,
            ]
        );

        // Create Suppliers
        $this\->command\->info('Creating suppliers...');
        $supplier1 = Supplier::firstOrCreate(
            ['tenant_id' => $company\->id, 'email' => 'delta@supplier.com'],
            [
                'name' => 'Delta Beverages Ltd',
                'contact_person' => 'John Moyo',
                'phone' => '+263771111111',
                'address' => '123 Industrial Road, Harare',
                'is_active' => true,
            ]
        );

        $supplier2 = Supplier::firstOrCreate(
            ['tenant_id' => $company\->id, 'email' => 'foods@supplier.com'],
            [
                'name' => 'National Foods',
                'contact_person' => 'Sarah Ncube',
                'phone' => '+263772222222',
                'address' => '456 Factory Street, Bulawayo',
                'is_active' => true,
            ]
        );

        $supplier3 = Supplier::firstOrCreate(
            ['tenant_id' => $company\->id, 'email' => 'oils@supplier.com'],
            [
                'name' => 'Olivine Industries',
                'contact_person' => 'David Chikwanha',
                'phone' => '+263773333333',
                'address' => '789 Processing Ave, Harare',
                'is_active' => true,
            ]
        );

        // Create Units of Measure
        $this\->command\->info('Creating units of measure...');
        $piece = UnitOfMeasure::firstOrCreate(
            ['tenant_id' => $company\->id, 'abbreviation' => 'pc'],
            [
                'name' => 'Piece',
                'is_base_unit' => true,
            ]
        );

        $dozen = UnitOfMeasure::firstOrCreate(
            ['tenant_id' => $company\->id, 'abbreviation' => 'doz'],
            [
                'name' => 'Dozen',
                'is_base_unit' => false,
            ]
        );

        $case = UnitOfMeasure::firstOrCreate(
            ['tenant_id' => $company\->id, 'abbreviation' => 'case'],
            [
                'name' => 'Case',
                'is_base_unit' => false,
            ]
        );

        $pack = UnitOfMeasure::firstOrCreate(
            ['tenant_id' => $company\->id, 'abbreviation' => 'pack'],
            [
                'name' => 'Pack',
                'is_base_unit' => false,
            ]
        );

        $kg = UnitOfMeasure::firstOrCreate(
            ['tenant_id' => $company\->id, 'abbreviation' => 'kg'],
            [
                'name' => 'Kilogram',
                'is_base_unit' => true,
            ]
        );

        $liter = UnitOfMeasure::firstOrCreate(
            ['tenant_id' => $company\->id, 'abbreviation' => 'L'],
            [
                'name' => 'Liter',
                'is_base_unit' => true,
            ]
        );

        $bottle = UnitOfMeasure::firstOrCreate(
            ['tenant_id' => $company\->id, 'abbreviation' => 'btl'],
            [
                'name' => 'Bottle',
                'is_base_unit' => true,
            ]
        );

        // Create Unit Conversions
        $this\->command\->info('Creating unit conversions...');
        // 1 Dozen = 12 Pieces
        UnitConversion::firstOrCreate([
            'tenant_id' => $company\->id,
            'from_unit_id' => $dozen\->id,
            'to_unit_id' => $piece\->id,
        ], [
            'conversion_factor' => 12,
        ]);

        // 1 Case = 24 Pieces
        UnitConversion::firstOrCreate([
            'tenant_id' => $company\->id,
            'from_unit_id' => $case\->id,
            'to_unit_id' => $piece\->id,
        ], [
            'conversion_factor' => 24,
        ]);

        // Create Products
        $this\->command\->info('Creating products...');

        // 1. Dragon Energy Drink
        $dragonImage = $this\->copyImageToStorage('dragon.jpg', 'dragon-energy-drink.jpg');
        $dragonDrink = Product::firstOrCreate(
            ['tenant_id' => $company\->id, 'sku' => 'BEV-DRA-001'],
            [
                'category_id' => $beverages\->id,
                'name' => 'Dragon Energy Drink',
                'barcode' => '6001087232456',
                'description' => 'Premium energy drink with taurine and caffeine',
                'unit' => 'piece',
                'cost_price' => 0.80,
                'selling_price' => 1.50,
                'tax_rate' => 15,
                'is_taxable' => true,
                'image' => $dragonImage,
                'track_stock' => true,
                'allow_decimal_qty' => false,
                'reorder_level' => 50,
                'reorder_quantity' => 200,
                'is_active' => true,
            ]
        );

        // Add units for Dragon Energy Drink
        ProductUnit::firstOrCreate([
            'product_id' => $dragonDrink\->id,
            'abbreviation' => 'pc',
        ], [
            'name' => 'Piece',
            'quantity' => 1,
            'selling_price' => 1.50,
            'cost_price' => 0.80,
            'is_default' => true,
            'sort_order' => 1,
        ]);

        ProductUnit::firstOrCreate([
            'product_id' => $dragonDrink\->id,
            'abbreviation' => 'doz',
        ], [
            'name' => 'Dozen',
            'quantity' => 12,
            'selling_price' => 16.00,
            'cost_price' => 9.60,
            'is_default' => false,
            'sort_order' => 2,
        ]);

        ProductUnit::firstOrCreate([
            'product_id' => $dragonDrink\->id,
            'abbreviation' => 'case',
        ], [
            'name' => 'Case',
            'quantity' => 24,
            'selling_price' => 30.00,
            'cost_price' => 19.20,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        // Add stock
        $this\->addStock($branch\->id, $dragonDrink\->id, 240); // 10 cases

        // Attach supplier
        $dragonDrink\->suppliers()\->syncWithoutDetaching([
            $supplier1\->id => [
                'supplier_sku' => 'DELTA-DRA-500',
                'cost_price' => 0.80,
                'is_primary' => true,
            ],
        ]);

        // 2. Mahatma Rice 2kg
        $riceImage = $this\->copyImageToStorage('mahatma.jpg', 'mahatma-rice-2kg.jpg');
        $rice = Product::firstOrCreate(
            ['tenant_id' => $company\->id, 'sku' => 'GRO-RIC-001'],
            [
                'category_id' => $groceries\->id,
                'name' => 'Mahatma Rice 2kg',
                'barcode' => '6001087345678',
                'description' => 'Premium long grain white rice',
                'unit' => 'pack',
                'cost_price' => 3.50,
                'selling_price' => 5.50,
                'tax_rate' => 0,
                'is_taxable' => false,
                'image' => $riceImage,
                'track_stock' => true,
                'allow_decimal_qty' => false,
                'reorder_level' => 20,
                'reorder_quantity' => 100,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate([
            'product_id' => $rice\->id,
            'abbreviation' => 'pack',
        ], [
            'name' => 'Pack',
            'quantity' => 1,
            'selling_price' => 5.50,
            'cost_price' => 3.50,
            'is_default' => true,
            'sort_order' => 1,
        ]);

        $this\->addStock($branch\->id, $rice\->id, 50);
        $rice\->suppliers()\->syncWithoutDetaching([
            $supplier2\->id => [
                'supplier_sku' => 'NF-MAH-2KG',
                'cost_price' => 3.50,
                'is_primary' => true,
            ],
        ]);

        // 3. Zimgold Cooking Oil 2L
        $zimgoldImage = $this\->copyImageToStorage('zimgold.jpg', 'zimgold-cooking-oil-2l.jpg');
        $cookingOilProduct = Product::firstOrCreate(
            ['tenant_id' => $company\->id, 'sku' => 'OIL-ZIM-001'],
            [
                'category_id' => $cookingOil\->id,
                'name' => 'Zimgold Cooking Oil 2L',
                'barcode' => '6001087456789',
                'description' => 'Pure vegetable cooking oil',
                'unit' => 'bottle',
                'cost_price' => 4.00,
                'selling_price' => 6.50,
                'tax_rate' => 0,
                'is_taxable' => false,
                'image' => $zimgoldImage,
                'track_stock' => true,
                'allow_decimal_qty' => false,
                'reorder_level' => 30,
                'reorder_quantity' => 100,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate([
            'product_id' => $cookingOilProduct\->id,
            'abbreviation' => 'btl',
        ], [
            'name' => 'Bottle',
            'quantity' => 1,
            'selling_price' => 6.50,
            'cost_price' => 4.00,
            'is_default' => true,
            'sort_order' => 1,
        ]);

        $this\->addStock($branch\->id, $cookingOilProduct\->id, 60);
        $cookingOilProduct\->suppliers()\->syncWithoutDetaching([
            $supplier3\->id => [
                'supplier_sku' => 'OLI-ZIM-2L',
                'cost_price' => 4.00,
                'is_primary' => true,
            ],
        ]);

        // 4. Sugar 2kg
        $sugarImage = $this\->copyImageToStorage('sugar.jpg', 'white-sugar-2kg.jpg');
        $sugar = Product::firstOrCreate(
            ['tenant_id' => $company\->id, 'sku' => 'GRO-SUG-001'],
            [
                'category_id' => $groceries\->id,
                'name' => 'White Sugar 2kg',
                'barcode' => '6001087567890',
                'description' => 'Fine white granulated sugar',
                'unit' => 'pack',
                'cost_price' => 2.00,
                'selling_price' => 3.50,
                'tax_rate' => 0,
                'is_taxable' => false,
                'image' => $sugarImage,
                'track_stock' => true,
                'allow_decimal_qty' => false,
                'reorder_level' => 25,
                'reorder_quantity' => 100,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate([
            'product_id' => $sugar\->id,
            'abbreviation' => 'pack',
        ], [
            'name' => 'Pack',
            'quantity' => 1,
            'selling_price' => 3.50,
            'cost_price' => 2.00,
            'is_default' => true,
            'sort_order' => 1,
        ]);

        $this\->addStock($branch\->id, $sugar\->id, 80);
        $sugar\->suppliers()\->syncWithoutDetaching([
            $supplier2\->id => [
                'supplier_sku' => 'NF-SUG-2KG',
                'cost_price' => 2.00,
                'is_primary' => true,
            ],
        ]);

        // 5. Red Wine 750ml
        $redWineImage = $this\->copyImageToStorage('redwine.jpg', 'nederburg-red-wine-750ml.jpg');
        $redWine = Product::firstOrCreate(
            ['tenant_id' => $company\->id, 'sku' => 'WIN-RED-001'],
            [
                'category_id' => $wines\->id,
                'name' => 'Nederburg Red Wine 750ml',
                'barcode' => '6001087678901',
                'description' => 'Premium South African red wine',
                'unit' => 'bottle',
                'cost_price' => 8.00,
                'selling_price' => 15.00,
                'tax_rate' => 15,
                'is_taxable' => true,
                'image' => $redWineImage,
                'track_stock' => true,
                'allow_decimal_qty' => false,
                'reorder_level' => 12,
                'reorder_quantity' => 50,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate([
            'product_id' => $redWine\->id,
            'abbreviation' => 'btl',
        ], [
            'name' => 'Bottle',
            'quantity' => 1,
            'selling_price' => 15.00,
            'cost_price' => 8.00,
            'is_default' => true,
            'sort_order' => 1,
        ]);

        $this\->addStock($branch\->id, $redWine\->id, 36);
        $redWine\->suppliers()\->syncWithoutDetaching([
            $supplier1\->id => [
                'supplier_sku' => 'WINE-NED-750',
                'cost_price' => 8.00,
                'is_primary' => true,
            ],
        ]);

        $this\->command\->info('✓ Products seeded successfully');
        $this\->command\->info('✓ 5 products created with stock and unit conversions');
    }

    /**
     * Copy image from public/demo to storage
     */
    private function copyImageToStorage(string $sourceFileName, string $destFileName): ?string
    {
        $sourcePath = public_path('demo/'.$sourceFileName);

        if (! File::exists($sourcePath)) {
            $this\->command\->warn("Image not found: {$sourcePath}");

            return null;
        }

        // Create the destination path
        $destPath = 'products/'.$destFileName;

        // Copy file to storage
        $fileContents = File::get($sourcePath);
        Storage::disk('public')\->put($destPath, $fileContents);

        $this\->command\->info("✓ Copied {$sourceFileName} to storage/app/public/{$destPath}");

        return $destPath;
    }

    /**
     * Add stock for a product
     */
    private function addStock(int $branchId, int $productId, int $quantity): void
    {
        Stock::firstOrCreate(
            [
                'branch_id' => $branchId,
                'product_id' => $productId,
                'variant_id' => null,
            ],
            [
                'quantity' => $quantity,
                'reserved_quantity' => 0,
            ]
        );

        $this\->command\->info("✓ Added {$quantity} units of stock");
    }
}
