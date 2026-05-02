<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);
        
        // Create regular user
        User::create([
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);
        
        // Create categories
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Latest gadgets and electronics'],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Fashion and apparel'],
            ['name' => 'Books', 'slug' => 'books', 'description' => 'Best selling books'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'description' => 'Home decor and garden supplies'],
        ];
        
        foreach ($categories as $cat) {
            Category::create($cat);
        }
        
        // Create sample products
        $products = [
            [
                'category_id' => 1,
                'name' => 'Wireless Headphones',
                'slug' => 'wireless-headphones',
                'description' => 'Premium wireless headphones with noise cancellation',
                'price' => 99.99,
                'compare_price' => 149.99,
                'stock_quantity' => 50,
                'sku' => 'WH-001',
                'is_featured' => true,
            ],
            [
                'category_id' => 1,
                'name' => 'Smart Watch',
                'slug' => 'smart-watch',
                'description' => 'Fitness tracker smart watch with heart rate monitor',
                'price' => 199.99,
                'compare_price' => 249.99,
                'stock_quantity' => 30,
                'sku' => 'SW-002',
                'is_featured' => true,
            ],
            [
                'category_id' => 2,
                'name' => 'Cotton T-Shirt',
                'slug' => 'cotton-tshirt',
                'description' => '100% premium cotton comfortable t-shirt',
                'price' => 24.99,
                'compare_price' => 39.99,
                'stock_quantity' => 100,
                'sku' => 'CT-003',
                'is_featured' => false,
            ],
        ];
        
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}