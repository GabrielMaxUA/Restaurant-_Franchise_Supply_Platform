<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ComprehensiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $adminUserId = DB::table('users')->insertGetId([
            'username' => 'admin',
            'password_hash' => Hash::make('password123'),
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'role_id' => 1,
            'status' => 1,
            'email_notifications_enabled' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create warehouse user
        $warehouseUserId = DB::table('users')->insertGetId([
            'username' => 'warehouse_manager',
            'password_hash' => Hash::make('warehouse123'),
            'email' => 'warehouse@example.com',
            'phone' => '4168560684',
            'role_id' => 2,
            'status' => 1,
            'email_notifications_enabled' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create franchisee user
        $franchiseeUserId = DB::table('users')->insertGetId([
            'username' => 'franchisee_demo',
            'password_hash' => Hash::make('franchisee123'),
            'email' => 'franchisee@example.com',
            'phone' => '4168560684',
            'role_id' => 3,
            'status' => 1,
            'email_notifications_enabled' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create admin details
        DB::table('admin_details')->insert([
            'user_id' => $adminUserId,
            'company_name' => 'Restaurant Franchise Supply Co.',
            'address' => '123 Main Street',
            'city' => 'Toronto',
            'state' => 'ON',
            'postal_code' => 'M1M 1M1',
            'phone' => '1234567890',
            'email' => 'admin@example.com',
            'website' => 'www.restaurantfranchisesupply.com',
            'created_by' => 'system',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create franchisee details
        DB::table('franchisee_details')->insert([
            'user_id' => $franchiseeUserId,
            'company_name' => 'Demo Restaurant',
            'address' => '456 Restaurant Ave',
            'city' => 'Toronto',
            'state' => 'ON',
            'postal_code' => 'M2M 2M2',
            'contact_name' => 'Demo Manager',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create sample categories
        $categories = [
            ['name' => 'Meat & Poultry', 'description' => 'Fresh meat and poultry products'],
            ['name' => 'Spices & Seasonings', 'description' => 'Herbs, spices and seasonings'],
            ['name' => 'Grains & Flour', 'description' => 'Flour, grains and baking ingredients'],
            ['name' => 'Dairy Products', 'description' => 'Milk, cheese and dairy items'],
            ['name' => 'Vegetables', 'description' => 'Fresh and frozen vegetables'],
        ];

        foreach ($categories as $category) {
            $category['created_at'] = now();
            $category['updated_at'] = now();
            DB::table('categories')->insert($category);
        }

        // Create sample products
        $products = [
            [
                'name' => 'Premium Chicken Breast',
                'description' => 'Organic, free-run chicken breast',
                'base_price' => 10.00,
                'category_id' => 1,
                'inventory_count' => 500,
            ],
            [
                'name' => 'Ground Beef',
                'description' => 'Canadian farm direct supplier (pack of 1kg)',
                'base_price' => 14.00,
                'category_id' => 1,
                'inventory_count' => 300,
            ],
            [
                'name' => 'Whole Pepper Mix',
                'description' => 'Mix of whole pepper (white, red, black, green) pack of 400gr',
                'base_price' => 32.00,
                'category_id' => 2,
                'inventory_count' => 150,
            ],
            [
                'name' => 'Wheat Flour',
                'description' => '3 kg pack',
                'base_price' => 12.00,
                'category_id' => 3,
                'inventory_count' => 200,
            ],
        ];

        foreach ($products as $product) {
            $product['created_at'] = now();
            $product['updated_at'] = now();
            DB::table('products')->insert($product);
        }

        $this->command->info('Sample data seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@example.com / password123');
        $this->command->info('Warehouse: warehouse@example.com / warehouse123');
        $this->command->info('Franchisee: franchisee@example.com / franchisee123');
    }
}